<?php

namespace Code16\JockoClient\Console;

use Code16\JockoClient\Facades\Jocko;
use Code16\JockoClient\JockoCms\Form\JockoField;
use Code16\JockoClient\JockoCms\JockoCollectionFormConfig;
use Code16\JockoClient\JockoCms\JockoCollectionListConfig;
use Code16\JockoClient\JockoCms\JockoCollectionConfig;
use Code16\JockoClient\JockoCms\List\JockoColumn;
use Illuminate\Console\Command;

class ConfigureCmsCommand extends Command
{
    protected $signature = 'jocko:configure-cms';
    protected $description = 'Send CMS configuration to Jocko.';

    public function handle(): void
    {
        collect(config('jocko-client.collections'))
            ->map(function ($collectionClassName, $k) {
                $model = new $collectionClassName;
                $collection = $collectionClassName::configureJockoCollection(new JockoCollectionConfig());
                $list = $collectionClassName::configureJockoCollectionList(new JockoCollectionListConfig());
                $form = $collectionClassName::configureJockoCollectionForm(new JockoCollectionFormConfig());

                return [
                    'key' => $model->jockoCollectionKey(),
                    'label' => $collection->label(),
                    'icon' => $collection->icon(),
                    'hasPublicationState' => $collection->hasPublicationState(),
                    'order' => $k+1,
                    'list' => [
                        'isReorderable' => $list->isReorderable(),
                        'isSearchable' => $list->isSearchable(),
                        'isPaginated' => $list->isPaginated(),
                        'columns' => $list
                            ->columns()
                            ->map(fn (JockoColumn $column) => [
                                'type' => $column->type(),
                                'key' => $column->key(),
                                'label' => $column->label(),
                                'size' => $column->size(),
                            ])
                    ],
                    'form' => [
                        'fields' => $form
                            ->customFields()
                            ->map(fn (JockoField $field) => [
                                'type' => $field->type(),
                                'key' => $field->key(),
                                'label' => $field->label(),
                                'validationRules' => $field->validationRules(),
                            ])
                    ],
                ];
            })
            ->each(function (array $collection) {
                $this->info('Update CMS configuration for [' . $collection['key'] . '].');
                Jocko::updateCollectionSharpConfiguration(
                    $collection['key'],
                    $collection
                );
            });

        $this->info('CMS configuration sent to Jocko.');
    }
}
