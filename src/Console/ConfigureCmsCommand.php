<?php

namespace Code16\OzuClient\Console;

use Code16\OzuClient\Client;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\List\OzuColumn;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Schema;

class ConfigureCmsCommand extends Command
{
    protected $signature = 'ozu:configure-cms';
    protected $aliases = ['ozu:configure', 'configure:ozu'];
    protected $description = 'Sends CMS configuration to Ozu.';
    private Client $ozuClient;

    private array $processedCollections = [];

    public function handle(Client $ozuClient): int
    {
        if (empty(config('ozu-client.collections'))) {
            $this->info('No collections to configure.');

            return self::SUCCESS;
        }

        $this->ozuClient = $ozuClient;

        $this->newLine();
        $this->line('<fg=white>Sending configuration to <options=bold>'.config('ozu-client.api_host').'</></>');
        $this->newLine();

        collect(config('ozu-client.collections'))
            ->each(fn ($collection, $k) => $this->updateCmsConfigurationFor($collection, $k+1));

        $this->newLine();
        $this->info('CMS configuration sent to Ozu.');

        return self::SUCCESS;
    }

    private function updateCmsConfigurationFor($collectionClass, int $order = 1, bool $isSubCollection = false): void
    {
        $model = match (true) {
            is_string($collectionClass) => app($collectionClass),
            is_callable($collectionClass) => $collectionClass(),
            default => $collectionClass,
        };

        // avoid processing the same collection twice
        if (in_array($model::class, $this->processedCollections)) {
            /*
             * We avoid processing multiple times the same subcollection, but we'll warn the
             * user only if it's not a subcollection, because subcollection models can be
             * declared in multiple parent collections.
             */

            if(!$isSubCollection) {
                $this->line('<fg=yellow>Skipping <options=bold>'.($model->ozuCollectionKey() ?? $model::class).'</> because it has already been processed.</>');
                $this->line('<fg=yellow>You may have wrongly configured your subcollections, or included a subcollection to the collections array in the ozu-client config file...</>');
            }

            return;
        } else { $this->processedCollections[] = $model::class; }

        $collection = $model::configureOzuCollection(new OzuCollectionConfig());
        $list = $model::configureOzuCollectionList(new OzuCollectionListConfig());
        $form = $model::configureOzuCollectionForm(new OzuCollectionFormConfig());

        $payload = [
            'key' => $model->ozuCollectionKey(),
            'label' => $collection->label(),
            'icon' => $collection->icon(),
            'isSubCollection' => !$isSubCollection,
            'hasPublicationState' => $collection->hasPublicationState(),
            'autoDeployDateField' => $collection->autoDeployDateField(),
            'isCreatable' => $collection->isCreatable(),
            'isDeletable' => $collection->isDeletable(),
            'subCollections' => $collection->subCollections()
                ->map(fn ($subCollectionClass) => app($subCollectionClass)->ozuCollectionKey()),
            'order' => $order,
            'list' => [
                'isReorderable' => $list->isReorderable(),
                'isSearchable' => $list->isSearchable(),
                'isPaginated' => $list->isPaginated(),
                'defaultSort' => $list->defaultSort(),
                'belongsToFilter' => $list->belongsToFilter()?->toArray(),
                'columns' => $list
                    ->columns()
                    ->map(fn (OzuColumn $column) => [
                        'type' => $column->type(),
                        'key' => $column->key(),
                        'label' => $column->label(),
                        'size' => $column->size(),
                    ]),
            ],
            'form' => [
                'title' => $form->titleField()?->toArray(),
                'cover' => $form->coverField()?->toArray(),
                'content' => $form->contentField()?->toArray(),
                'fields' => $form
                    ->customFields()
                    ->map(fn (OzuField $field) => $field->toArray()),
            ],
            'customFields' => collect(Schema::getColumnListing($model->getTable()))
                ->filter(fn (string $column) => !in_array($column, $model::$ozuColumns))
                ->mapWithKeys(fn (string $column) => [
                    $column => match (Schema::getColumnType($model->getTable(), $column)) {
                        'datetime', 'timestamps' => 'dateTime',
                        'date' => 'date',
                        'int', 'bigint', 'smallint', 'mediumint', 'tinyint' => 'integer',
                        'float', 'double' => 'float',
                        'text', 'json' => 'text',
                        default => 'string',
                    },
                ]),
        ];

        $this->line('<fg=green>Updating CMS configuration for <options=bold>'.$payload['key'].'</>...</>');

        try {
            $this->ozuClient->updateCollectionSharpConfiguration($payload['key'], $payload);

            $collection->subCollections()
                ->each(fn ($subCollectionClass) => $this
                    ->updateCmsConfigurationFor($subCollectionClass, isSubCollection: true)
                );

        } catch (RequestException $e) {
            if ($message = $e->response->json()) {
                if (!isset($message['message'])) {
                    throw $e;
                }

                // Display by priority: validations errors, generic error, json dump of the response
                $this->error(sprintf(
                    '[%s] %s',
                    $collection['key'],
                    isset($message['errors']) ?
                        collect(is_array($message['errors']) ? $message['errors'] : [])
                            ->map(fn ($error, $key) => sprintf('%s: %s', $key, $error[0]))
                            ->implode(', ') ?? ($message['message'] ?? json_encode($message))
                        : ($message['message'] ?? json_encode($message))
                ));
            } else {
                throw $e;
            }
        }
    }
}
