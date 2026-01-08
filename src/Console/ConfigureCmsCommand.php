<?php

namespace Code16\OzuClient\Console;

use Closure;
use Code16\OzuClient\Client;
use Code16\OzuClient\Exceptions\OzuConfigureCmsException;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\List\OzuColumn;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Code16\OzuClient\OzuCms\OzuSettingsFormConfig;
use Code16\OzuClient\Support\Settings\OzuSiteSettings;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;

class ConfigureCmsCommand extends Command
{
    protected $signature = 'ozu:configure';
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

        $this->configureCollections();

        if (!empty(config('ozu-client.settings'))) {
            $this->info('Syncing settings configuration with Ozu...');
            $this->newLine();

            $this->configureSettings();
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
                try {
                    $this->updateCmsConfigurationFor($model, $k + 1);
                } catch (OzuConfigureCmsException $e) {
                    $this->error($e->getMessage());
                }
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

    private function updateCmsConfigurationFor($model, int $order = 1, bool $isSubCollection = false): void
    {
        // Avoid processing the same collection twice
        if (in_array($model::class, $this->processedCollections)) {
            /*
             * We avoid processing multiple times the same subcollection, but we'll warn the
             * user only if it's not a subcollection, because subcollection models can be
             * declared in multiple parent collections.
             */
            if (!$isSubCollection) {
                $this->line(
                    '<fg=yellow>Skipping <options=bold>'
                    .($model->ozuCollectionKey() ?? $model::class)
                    .'</> because it has already been processed.</>'
                );
                $this->line(
                    '<fg=yellow>You may have wrongly configured your subcollections, or included a subcollection to the collections array in the ozu-client config file...</>'
                );
            }

            return;

        } else {
            $this->processedCollections[] = $model::class;
        }

        $this->line('<fg=green>Update CMS configuration for <options=bold>'.$model->ozuCollectionKey().'</>...</>');

        $customFields = collect(Schema::getColumnListing($model->getTable()))
            ->filter(fn (string $column) => !in_array($column, $model::$ozuColumns))
            ->mapWithKeys(fn (string $column) => [
                $column => match (Schema::getColumnType($model->getTable(), $column)) {
                    'datetime', 'time' => 'dateTime',
                    'date' => 'date',
                    'int', 'bigint', 'smallint', 'mediumint', 'tinyint' => 'integer',
                    'float', 'double' => 'float',
                    'text', 'json' => 'text',
                    'boolean' => 'boolean',
                    default => 'string',
                },
            ]);

        /** @var OzuCollectionConfig $collection */
        $collection = $model::configureOzuCollection(new OzuCollectionConfig());
        /** @var OzuCollectionListConfig $list */
        $list = $model::configureOzuCollectionList(new OzuCollectionListConfig());
        /** @var OzuCollectionFormConfig $form */
        $form = $model::configureOzuCollectionForm(new OzuCollectionFormConfig());

        $this->guardForUnknownKeys($model, $customFields, $list->columns());
        $this->guardForUnknownKeys($model, $customFields, $form->customFields());

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
                    ->mapWithKeys(fn (OzuField $field, int $key) => [$field->key() => [
                        'order' => $key,
                        ...$field->toArray(),
                    ]]),
            ],
            'customFields' => $customFields,
        ];

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
                ->each(fn ($subCollectionClass) => $this->updateCmsConfigurationFor(
                    $subCollectionClass,
                    isSubCollection: true
                ));

        } catch (RequestException $e) {
            if ($message = $e->response->json()) {
                if (!isset($message['message'])) {
                    throw $e;
                }

                // Display by priority: validations errors then generic error
                $this->error(sprintf(
                    '[%s] %s',
                    $payload['key'],
                    isset($message['errors']) && is_array($message['errors'])
                        ? collect($message['errors'])
                            ->map(fn ($error, $key) => sprintf('%s: %s', $key, $error[0]))
                            ->implode(', ')
                        : $message['message']
                ));
            } else {
                throw $e;
            }
        }
    }

    private function configureSettings(): void
    {
        /** @var OzuSiteSettings $settingsClass */
        $settingsClass = app(config('ozu-client.settings'));
        $configuration = $settingsClass::configureSettingsForm(new OzuSettingsFormConfig());

        try {
            $this->ozuClient->updateSettingsSharpConfiguration([
                'fields' => $configuration->fields()
                    ?->map(fn (OzuField $field) => $field->toArray())
                    ->toArray()
                    ?? [],
            ]
            );
        } catch (RequestException $e) {
            if (($message = $e->response->json()) && isset($message['message'])) {
                $this->error('['
                    .(class_basename(config('ozu-client.settings')) ?? 'SETTINGS')
                    .'] '
                    .$message['message']
                );

                return;
            }
            throw $e;
        }
    }

    private function guardForUnknownKeys($model, $customFields, Collection $fieldList): void
    {
        $knownKeys = [
            ...$model::$ozuColumns,
            ...array_keys($customFields->toArray()),
            'cover',
            ...$this->getMediaMorphKeysFor($model),
        ];

        $unknownKeys = $fieldList
            ->map(fn ($ozuField) => $ozuField->key())
            ->filter(fn (string $ozuColumnKey) => !in_array($ozuColumnKey, $knownKeys))
            ->toArray();

        throw_if(
            count($unknownKeys) > 0,
            OzuConfigureCmsException::unknownKeys($model::class, $unknownKeys)
        );
    }

    private function getMediaMorphKeysFor(Model $model): array
    {
        $reflection = new ReflectionClass($model);
        $relations = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== get_class($model) || $method->getNumberOfParameters() > 0) {
                continue;
            }

            try {
                $relation = $method->invoke($model);
            } catch (\Throwable) {
                continue;
            }

            if ($relation instanceof MorphOne || $relation instanceof MorphMany) {
                $relations[] = $method->getName();
            }
        }

        return $relations;
    }
}
