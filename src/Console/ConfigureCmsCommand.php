<?php

namespace Code16\OzuClient\Console;

use Closure;
use Code16\OzuClient\Client;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\List\OzuColumn;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Code16\OzuClient\OzuCms\OzuSettingsFormConfig;
use Code16\OzuClient\Support\Settings\OzuSiteSettings;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Schema;

class ConfigureCmsCommand extends Command
{
    protected $signature = 'ozu:configure-cms';

    protected $aliases = ['ozu:configure'];

    protected $description = 'Send CMS configuration to Ozu.';

    public function handle(Client $ozuClient): int
    {
        if (empty(config('ozu-client.collections')) && empty(config('ozu-client.settings'))) {
            $this->info('No collection or settings to configure.');

            return self::SUCCESS;
        }
        $this->info('Syncing collections’ configuration with Ozu...');
        $this->newLine();

        $this->configureCollections($ozuClient);

        if (!empty(config('ozu-client.settings'))) {
            $this->info('Syncing settings configuration with Ozu...');
            $this->newLine();

            $this->configureSettings($ozuClient);
        }

        $this->info('CMS configuration sent to Ozu.');

        return self::SUCCESS;
    }

    private function configureCollections(Client $ozuClient): void
    {
        $collectionModels = collect(config('ozu-client.collections'))
            ->map(fn ($collection) => match (true) {
                is_string($collection) => app($collection),
                $collection instanceof Closure => $collection(),
                default => $collection
            });

        $collectionModels
            ->map(function ($model, $k) {
                $collection = $model::configureOzuCollection(new OzuCollectionConfig());
                $list = $model::configureOzuCollectionList(new OzuCollectionListConfig());
                $form = $model::configureOzuCollectionForm(new OzuCollectionFormConfig());

                return [
                    'key' => $model->ozuCollectionKey(),
                    'label' => $collection->label(),
                    'icon' => $collection->icon(),
                    'hasPublicationState' => $collection->hasPublicationState(),
                    'autoDeployDateField' => $collection->autoDeployDateField(),
                    'isCreatable' => $collection->isCreatable(),
                    'isDeletable' => $collection->isDeletable(),
                    'order' => $k + 1,
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
            })
            ->each(function (array $collection) use ($ozuClient) {
                $this->info('Update CMS configuration for ['.$collection['key'].'].');
                try {
                    $ozuClient->updateCollectionSharpConfiguration(
                        $collection['key'],
                        $collection
                    );
                } catch (RequestException $e) {
                    if ($message = $e->response->json()) {
                        if (!isset($message['message'])) {
                            throw $e;
                        }
                        $this->error('['.$collection['key'].'] '.$message['message']);
                    } else {
                        throw $e;
                    }
                }
            });

        $ozuClient->deleteCollectionSharpConfigurationExcept(
            $collectionModels
                ->map(fn($model) => $model->ozuCollectionKey())
                ->toArray()
        );
    }

    private function configureSettings(Client $ozuClient): void
    {
        /** @var OzuSiteSettings $settingsClass */
        $settingsClass = app(config('ozu-client.settings'));

        $configuration = $settingsClass::configureSettingsForm(new OzuSettingsFormConfig());

        try {
            $ozuClient->updateSettingsSharpConfiguration(
                ['fields' => $configuration->fields()
                        ?->map(fn (OzuField $field) => $field->toArray())
                        ->toArray()
                    ?? []]
            );
        } catch (RequestException $e) {
            if ($message = $e->response->json()) {
                if (!isset($message['message'])) {
                    throw $e;
                }
                $this->error('['.(class_basename(config('ozu-client.settings')) ?? 'SETTINGS').'] '.$message['message']);
            } else {
                throw $e;
            }
        }
    }
}
