<?php

namespace Code16\OzuClient\Console;

use Code16\OzuClient\Client;
use Code16\OzuClient\Exceptions\OzuClientException;
use Illuminate\Console\Command;
use Illuminate\Support\Uri;
use Laravel\Prompts\Support\Logger;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\task;
use function Laravel\Prompts\warning;

class DeployCommand extends Command
{
    protected $signature = 'ozu:deploy';
    protected $description = 'Triggers a deployment.';

    protected ?string $deploymentTargetUrl = null;
    private Client $ozuClient;

    public function handle(Client $ozuClient)
    {
        $this->ozuClient = $ozuClient;

        /* Ask for deploy confirmation */
        $confirmed = confirm(
            label: 'Are you sure you want to deploy your website?',
        );

        if (!$confirmed) {
            $this->info('Deployment cancelled.');

            return self::SUCCESS;
        }

        clear();
        $this->newLine(2);

        /* Ask for ozu:configure before deploying confirmation */
        $syncProject = confirm(
            label: 'Do you want to sync your project with Ozu before deploying?',
            hint: "This will execute a ozu:configure before, do it if you made changes to your project's code."
        );

        if ($syncProject && $this->call('ozu:configure') !== 0) {
            error('Failed to sync your project with Ozu.');

            return self::FAILURE;
        }

        clear();
        $this->newLine(2);

        /* Ask for which deployment target to deploy to */
        $targetId = $this->getTarget();

        if ($targetId === null) {
            return self::FAILURE;
        }

        clear();
        $this->newLine(2);

        try {
            /* Ask for unpublished posts deployment confirmation */
            $this->getUnpublishedPostsConfirmation($targetId);
        } catch (Throwable $e) {
            error($e->getMessage());

            return self::FAILURE;
        }

        clear();
        $this->newLine(2);

        try {
            $this->triggerDeployment($targetId);
        } catch (Throwable $e) {
            error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function getTarget(): ?int
    {
        try {
            $targets = $this->ozuClient->getDeploymentTargets();
        } catch (OzuClientException $e) {
            error($e->getMessage());
            $this->fail();
        }

        $targets = collect($targets['data'] ?? []);

        if ($targets->isEmpty()) {
            error('No deployment targets found.');
            $this->fail();
        }

        if (collect($targets)->filter(fn ($target) => $target['status'] === 'ready')->count() === 0) {
            error('No ready deployment targets found.');
            $this->fail();
        }

        $target = select(
            label: 'Select a deployment target to deploy to:',
            options: collect($targets)
                ->filter(fn ($target) => $target['status'] === 'ready')
                ->mapWithKeys(fn ($target) => [$target['id'] => sprintf('[%s] %s', $target['driver'], $target['name'])]),
            info: function ($target) use ($targets) {
                $target = collect($targets)->firstWhere('id', $target);

                return !empty($target['website_url']) ?
                    sprintf(
                        'Deploying to %s',
                        $target['website_url'],
                    ) : null;
            }
        );

        $this->deploymentTargetUrl = collect($targets)->firstWhere('id', $target)['website_url'] ?? null;

        return $target;
    }

    private function triggerDeployment(int $targetId): void
    {

        try {
            /** @var string|null $deploymentUuid */
            $deploymentUuid = fluent($this->ozuClient->triggerDeployment($targetId))->get('data.uuid', null);

            if (!$deploymentUuid) {
                $this->fail('Deployment failed.');
            }

            task('Deploying...', function (Logger $logger) use (&$deploymentUuid, &$status) {
                $offset = 0;
                $status = 'pending';

                while (in_array($status, ['pending', 'waiting'])) {
                    try {
                        $response = $this->ozuClient->fetchDeploymentLogs($deploymentUuid, $offset);

                        if ($response) {
                            $status = $response['status'] ?? 'pending';
                            $offset = $response['offset'] ?? $offset;
                            $progression = $response['progression'] ?? 0;
                            $step = $response['step'] ?? '';

                            if ($step) {
                                $logger->subLabel(sprintf(
                                    '%s (%d%%)',
                                    $step,
                                    $progression
                                ));
                            }

                            if (!empty($response['logs'])) {
                                foreach (explode("\n", trim($response['logs'])) as $line) {
                                    if ($line) {
                                        $logger->line($this->stripAnsi($line));
                                    }
                                }
                            }
                        }
                    } catch (Throwable $e) {
                        $logger->warning('Error fetching deployment logs: '.$e->getMessage());
                    }

                    if ($status === 'pending') {
                        if (config('app.env') !== 'testing') {
                            sleep(1);
                        }
                    }
                }
            },
                limit: 20,
                subLabel: 'Waiting for deployment logs to be available...',
            );

            $this->newLine(2);

            match ($status) {
                'success' => info('Deployment successful !'),
                'failed' => error('Deployment failed.'),
                default => warning('Unable to fetch deployment status.')
            };

            note(
                sprintf('Access your deployment logs and information at %s',
                    Uri::of(config('ozu-client.api_host'))
                        ->withPath(
                            sprintf('/dashboard/%s/s-show/config/s-show/websiteDeploymentTargets/%s/s-show/deployments/%s',
                                (config('ozu-client.website_key') ?: $this->ozuClient->getWebsiteKey()) ?: 'root',
                                $targetId,
                                $deploymentUuid,
                            )
                        )
                        ->toString()
                )
            );

        } catch (OzuClientException $e) {
            $this->fail($e->getMessage());
        }
    }

    private function getUnpublishedPostsConfirmation(int $targetId): void
    {
        try {
            $unpublishedPosts = $this->ozuClient->getUndeployedPostsForTarget($targetId);
        } catch (OzuClientException $e) {
            error($e->getMessage());
            if (confirm(
                label: 'Do you want to still publish without knowing if there are unpublished posts?',
            )) {
                return;
            } else {
                $this->fail();
            }
        }

        if (empty($unpublishedPosts['data'] ?? [])) {
            return;
        }

        warning('The following posts are not published on the selected target, confirming will publish them:');

        table(
            headers: ['ID', 'Title', 'Collection'],
            rows: collect($unpublishedPosts['data'])->map(fn ($post) => [$post['id'], ($post['label'] ?? $post['title']), ucfirst($post['collection_key'])]),
        );

        $deployUnpublishedPosts = confirm(
            label: 'Deploying will publish all unpublished posts, do you want to continue?',
            hint: 'Unpublished posts will be published on the selected target.',
        );

        if (!$deployUnpublishedPosts) {
            $this->fail('Deployment cancelled.');
        }
    }

    private function stripAnsi(string $text): string
    {
        // Remove real ANSI escape sequences
        $text = preg_replace(
            '/\x1B\[[0-9;]*[A-Za-z]/',
            '',
            $text
        );

        // Remove visual escape representations like ␛[
        $text = preg_replace(
            '/␛\[[0-9;]*[A-Za-z]/u',
            '',
            $text
        );

        return $text;
    }
}
