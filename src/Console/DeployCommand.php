<?php

namespace Code16\OzuClient\Console;

use Code16\OzuClient\Client;
use Code16\OzuClient\Exceptions\OzuClientException;
use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\stream;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class DeployCommand extends Command
{
    protected $signature = 'ozu:deploy';
    protected $description = 'Trigger a deployment.';

    protected ?string $deploymentTargetUrl = null;

    protected $targets = [];
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

        if (!$targets) {
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
        info('Triggering deployment...');

        try {
            /** @var string|null $deploymentUuid */
            $deploymentUuid = fluent($this->ozuClient->triggerDeployment($targetId))->get('data.uuid', null);

            if (!$deploymentUuid) {
                $this->fail('Deployment failed.');
            }

            $stream = stream();

            $status = null;
            $lastStatusCheck = 0;

            $this->ozuClient->streamDeploymentLogs(
                $deploymentUuid,
                function (string $chunk) use (
                    $stream,
                    $deploymentUuid,
                    &$status,
                    &$lastStatusCheck
                ) {
                    // Check deployment status every 5 seconds
                    if ((time() - $lastStatusCheck) >= 5) {

                        $lastStatusCheck = time();

                        $status = $this->ozuClient
                            ->fetchDeploymentStatus($deploymentUuid);

                        if (in_array(
                            $status['status'] ?? null,
                            ['success', 'failed'],
                            true
                        )) {
                            return false;
                        }
                    }

                    try {
                        $data = json_decode($chunk, true, flags: JSON_THROW_ON_ERROR);

                        match ($data['type'] ?? null) {
                            'ping' => null,
                            'message' => !empty($data['message'])
                                ? $stream->append(
                                    $stream->white(
                                        $this->stripAnsi($data['message'])
                                    )
                                 ."\n"
                                )
                                : null,

                            default => $stream->append(
                                $stream->white(
                                    $this->stripAnsi($chunk)
                                )
                                ."\n"
                            ),
                        };

                    } catch (Throwable $e) {
                        $stream->append(
                            $stream->bgRed(
                                $stream->white(
                                    $chunk."\n"
                                )
                            )
                        );
                    }

                    return true;
                }
            );

            // Final status refresh
            $status ??= $this->ozuClient
                ->fetchDeploymentStatus($deploymentUuid);

            match ($status['status'] ?? null) {
                'success' => info(sprintf('Deployment finished successfully!%s', $this->deploymentTargetUrl ? sprintf(' Visit %s to see the changes.', $this->deploymentTargetUrl) : '')),
                'failed' => error('Deployment failed.'),
                default => error('Deployment ended with unknown status.'),
            };
        } catch (OzuClientException $e) {
            $this->fail($e->getMessage());

            return;
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
