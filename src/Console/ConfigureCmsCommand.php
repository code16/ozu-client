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
    protected $description = 'Send CMS configuration to Ozu.';
    private Client $ozuClient;

    public function handle(Client $ozuClient): int
    {
        if (empty(config('ozu-client.collections'))) {
            $this->info('No collections to configure.');

            return self::SUCCESS;
        }

        $this->ozuClient = $ozuClient;

        collect(config('ozu-client.collections'))
            ->each(fn ($collection, $k) => $this->updateCmsConfigurationFor($collection, $k+1));

        $this->info('CMS configuration sent to Ozu.');

        return self::SUCCESS;
    }

    private function updateCmsConfigurationFor($collectionClass, int $order = 1, bool $menu = true): void
    {
        $model = match (true) {
            is_string($collectionClass) => app($collectionClass),
            is_callable($collectionClass) => $collectionClass(),
            default => $collectionClass,
        };

        $collection = $model::configureOzuCollection(new OzuCollectionConfig());
        $list = $model::configureOzuCollectionList(new OzuCollectionListConfig());
        $form = $model::configureOzuCollectionForm(new OzuCollectionFormConfig());

        $payload = [
            'key' => $model->ozuCollectionKey(),
            'label' => $collection->label(),
            'icon' => $collection->icon(),
            'isMenu' => $menu,
            'hasPublicationState' => $collection->hasPublicationState(),
            'autoDeployDateField' => $collection->autoDeployDateField(),
            'isCreatable' => $collection->isCreatable(),
            'isDeletable' => $collection->isDeletable(),
            'subCollections' => $collection->subCollections(),
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

        $this->info('Update CMS configuration for ['.$payload['key'].'].');

        try {
            $this->ozuClient->updateCollectionSharpConfiguration($payload['key'], $payload);

            $collection->subCollections()
                ->keys()
                ->each(fn ($subCollectionClass) => $this
                    ->updateCmsConfigurationFor($subCollectionClass, menu: false)
                );

        } catch (RequestException $e) {
            if ($message = $e->response->json()) {
                if (!isset($message['message'])) {
                    throw $e;
                }
                $this->error('['.$payload['key'].'] '.$message['message']);
            } else {
                throw $e;
            }
        }
    }
}
