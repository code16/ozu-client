<?php

namespace Code16\OzuClient\Console;

use Code16\OzuClient\Facades\Ozu;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\List\OzuColumn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ConfigureCmsCommand extends Command
{
    protected $signature = 'ozu:configure-cms';
    protected $description = 'Send CMS configuration to Ozu.';

    public function handle(): void
    {
        if (empty(config('ozu-client.collections'))) {
            $this->info('No collections to configure.');
            return;
        }

        collect(config('ozu-client.collections'))
            ->map(function ($collectionClassName, $k) {
                $model = new $collectionClassName;
                $collection = $collectionClassName::configureOzuCollection(new OzuCollectionConfig());
                $list = $collectionClassName::configureOzuCollectionList(new OzuCollectionListConfig());
                $form = $collectionClassName::configureOzuCollectionForm(new OzuCollectionFormConfig());

                return [
                    'key' => $model->ozuCollectionKey(),
                    'label' => $collection->label(),
                    'icon' => $collection->icon(),
                    'hasPublicationState' => $collection->hasPublicationState(),
                    'isCreatable' => $collection->isCreatable(),
                    'isDeletable' => $collection->isDeletable(),
                    'order' => $k+1,
                    'list' => [
                        'isReorderable' => $list->isReorderable(),
                        'isSearchable' => $list->isSearchable(),
                        'isPaginated' => $list->isPaginated(),
                        'columns' => $list
                            ->columns()
                            ->map(fn (OzuColumn $column) => [
                                'type' => $column->type(),
                                'key' => $column->key(),
                                'label' => $column->label(),
                                'size' => $column->size(),
                            ])
                    ],
                    'form' => [
                        'fields' => $form
                            ->customFields()
                            ->map(fn (OzuField $field) => $field->toArray())
                    ],
                    'customFields' => collect(Schema::getColumnListing($model->getTable()))
                        ->filter(fn (string $column) => !in_array($column, $model::$ozuColumns))
                        ->mapWithKeys(fn (string $column) => [
                            $column => match(Schema::getColumnType($model->getTable(), $column)) {
                                'datetime', 'timestamps' => 'dateTime',
                                'date' => 'date',
                                'int', 'bigint', 'smallint', 'mediumint', 'tinyint' => 'integer',
                                'float', 'double' => 'float',
                                default => 'string',
                            }
                        ])
                ];
            })
            ->each(function (array $collection) {
                $this->info('Update CMS configuration for [' . $collection['key'] . '].');
                Ozu::updateCollectionSharpConfiguration(
                    $collection['key'],
                    $collection
                );
            });

        $this->info('CMS configuration sent to Ozu.');
    }
}
