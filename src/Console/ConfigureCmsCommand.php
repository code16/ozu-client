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
    protected $aliases = ['ozu:configure', 'configure:ozu'];
    protected $description = 'Send CMS configuration to Ozu.';
    private Client $ozuClient;
    private array $processedCollections = [];

    public function handle(Client $ozuClient): int
    {
        if (empty(config('ozu-client.collections')) && empty(config('ozu-client.settings'))) {
            $this->info('No collection or settings to configure.');

            return self::SUCCESS;
        }

        $this->ozuClient = $ozuClient;

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

    private function configureCollections(): void
    {
        $collectionModels = collect(config('ozu-client.collections'))
            ->map(fn ($collection) => match (true) {
                is_string($collection) => app($collection),
                $collection instanceof Closure => $collection(),
                default => $collection
            });

        $collectionModels
            ->each(function ($model, $k) {
                $this->updateCmsConfigurationFor($model, $k + 1);
            });

        $this->ozuClient->deleteCollectionSharpConfigurationExcept(
            $collectionModels
                ->map(function ($model) {
                    $sub = collect(
                        $model::configureOzuCollection(new OzuCollectionConfig())
                            ->subCollections()
                    )->map(fn ($subModel) => match (true) {
                        is_string($subModel) => app($subModel),
                        $subModel instanceof Closure => $subModel(),
                        default => $subModel
                    });

                    return collect([$model])->merge($sub);
                })
                ->flatten()
                ->map(fn ($model) => $model->ozuCollectionKey())
                ->toArray()
        );
    }

    private function updateCmsConfigurationFor($model, int $order = 1, bool $isSubCollection = false)
    {
        // avoid processing the same collection twice
        if (in_array($model::class, $this->processedCollections)) {
            /*
             * We avoid processing multiple times the same subcollection, but we'll warn the
             * user only if it's not a subcollection, because subcollection models can be
             * declared in multiple parent collections.
             */
            if (!$isSubCollection) {
                $this->line(
                    '<fg=yellow>Skipping <options=bold>'.($model->ozuCollectionKey(
                    ) ?? $model::class).'</> because it has already been processed.</>'
                );
                $this->line(
                    '<fg=yellow>You may have wrongly configured your subcollections, or included a subcollection to the collections array in the ozu-client config file...</>'
                );

            }

            return;

        } else {
            $this->processedCollections[] = $model::class;
        }

        /** @var OzuCollectionConfig $collection */
        $collection = $model::configureOzuCollection(new OzuCollectionConfig());
        /** @var OzuCollectionListConfig $list */
        $list = $model::configureOzuCollectionList(new OzuCollectionListConfig());
        /** @var OzuCollectionFormConfig $form */
        $form = $model::configureOzuCollectionForm(new OzuCollectionFormConfig());

        $payload = [
            'key' => $model->ozuCollectionKey(),
            'label' => $collection->label(),
            'icon' => $collection->icon(),
            'isSubCollection' => $isSubCollection,
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
                    ->mapWithKeys(fn (OzuColumn $column, int $key) => [$column->key() => [
                        'type' => $column->type(),
                        'order' => $key,
                        'key' => $column->key(),
                        'label' => $column->label(),
                        'size' => $column->size(),
                    ]]),
            ],
            'form' => [
                'title' => $form->titleField()?->toArray(),
                'cover' => $form->coverField()?->toArray(),
                'content' => $form->contentField()?->toArray(),
                'fields' => $form
                    ->customFields()
                    ->mapWithKeys(fn (OzuField $field, int $key) => [$field->getKey() => [
                        'order' => $key,
                        ...$field->toArray(),
                    ]]),
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

        $this->line('<fg=green>Update CMS configuration for <options=bold>'.$payload['key'].'</>...</>');
        try {
            $this->ozuClient->updateCollectionSharpConfiguration(
                $payload['key'],
                $payload
            );

            $collection->subCollections()
                ->map(fn ($subCollectionClass) => match (true) {
                    is_string($subCollectionClass) => app($subCollectionClass),
                    $subCollectionClass instanceof Closure => $subCollectionClass(),
                    default => $subCollectionClass
                })
                ->each(fn ($subCollectionClass) => $this->updateCmsConfigurationFor($subCollectionClass, isSubCollection: true));

        } catch (RequestException $e) {
            if ($message = $e->response->json()) {
                if (!isset($message['message'])) {
                    throw $e;
                }

                // Display by priority: validations errors, generic error, json dump of the response
                $this->error(sprintf(
                    '[%s] %s',
                    $payload['key'],
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
