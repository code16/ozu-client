<?php

use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\List\OzuColumn;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Code16\OzuClient\Tests\Fixtures\DummySubcollectionTestModel;
use Code16\OzuClient\Tests\Fixtures\DummyTestModel;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Request;

beforeEach(function () {
    config(['ozu-client.website_key' => 'test']);
    Http::fake();

    Schema::swap(new class
    {
        public function getColumnListing($table): array
        {
            return ['dummy-text', 'dummy-check', 'dummy-image', 'dummy-date', 'dummy-select'];
        }

        public function getColumnType($table, $column): string
        {
            return match ($column) {
                'dummy-check' => 'tinyint',
                'dummy-date' => 'date',
                default => 'string',
            };
        }
    });
});

it('sends cms configuration to Ozu for each configured collection', function () {
    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy1';
            }
        },
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy2';
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'dummy1'
        );
    });

    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'dummy2'
        );
    });
});

it('sends general cms configuration to Ozu', function () {
    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy';
            }

            public static function configureOzuCollection(OzuCollectionConfig $config): OzuCollectionConfig
            {
                return $config
                    ->setLabel('Dummy label')
                    ->setIcon('dummy-icon')
                    ->setHasPublicationState()
                    ->setAutoDeployDateField('dummy_date')
                    ->setIsCreatable()
                    ->setIsDeletable();
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'dummy'
        )
            && $request['label'] == 'Dummy label'
            && $request['icon'] == 'dummy-icon'
            && $request['hasPublicationState'] == true
            && $request['isCreatable'] == true
            && $request['autoDeployDateField'] == 'dummy_date'
            && $request['isDeletable'] == true;
    });
});

it('sends list cms configuration to Ozu', function () {
    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy';
            }

            public static function configureOzuCollectionList(OzuCollectionListConfig $config): OzuCollectionListConfig
            {
                return $config
                    ->setIsPaginated()
                    ->setIsReorderable()
                    ->setIsSearchable()
                    ->addColumn(OzuColumn::makeText('dummy-text', 1)->setLabel('Dummy text'))
                    ->addColumn(OzuColumn::makeCheck('dummy-check', 2)->setLabel('Dummy check'))
                    ->addColumn(OzuColumn::makeImage('dummy-image', 3)->setLabel('Dummy image'))
                    ->addColumn(OzuColumn::makeDate('dummy-date', 3)->setLabel('Dummy date')->setDefaultSort());
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn (Request $request) => $request['list']['isReorderable'] == true
            && $request['list']['isSearchable'] == true
            && $request['list']['isPaginated'] == true
            && $request['list']['defaultSort'] == ['column' => 'dummy-date', 'direction' => 'asc']
            && $request['list']['columns'] == collect([
                'dummy-text' => [
                    'type' => 'text',
                    'key' => 'dummy-text',
                    'label' => 'Dummy text',
                    'size' => 1,
                    'order' => 0,
                ],
                'dummy-check' => [
                    'type' => 'check',
                    'key' => 'dummy-check',
                    'label' => 'Dummy check',
                    'size' => 2,
                    'order' => 1,
                ],
                'dummy-image' => [
                    'type' => 'image',
                    'key' => 'dummy-image',
                    'label' => 'Dummy image',
                    'size' => 3,
                    'order' => 2,
                ],
                'dummy-date' => [
                    'type' => 'date',
                    'key' => 'dummy-date',
                    'label' => 'Dummy date',
                    'size' => 3,
                    'order' => 3,
                ],
            ]),
        fn (Request $request) => $request->method() == 'DELETE',
    ]);
});

it('sends form cms configuration to Ozu', function () {
    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy';
            }

            public static function configureOzuCollectionForm(OzuCollectionFormConfig $config): OzuCollectionFormConfig
            {
                return $config
                    ->addCustomField(
                        OzuField::makeText('dummy-text')
                            ->setLabel('Dummy text')
                            ->setValidationRules(['required'])
                    )
                    ->addCustomField(
                        OzuField::makeSelect('dummy-select')
                            ->setDisplayAsDropdown()
                            ->setOptions([1 => 'option1', 2 => 'option2'])
                            ->setLabel('Dummy select')
                            ->setHelpMessage('Select an option')
                    );
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn (Request $request) => $request['form']['fields'] == collect([
            'dummy-text' => [
                'type' => 'text',
                'key' => 'dummy-text',
                'label' => 'Dummy text',
                'validationRules' => ['required'],
                'helpMessage' => null,
                'isUpdatable' => true,
                'order' => 0,
            ],
            'dummy-select' => [
                'type' => 'select',
                'key' => 'dummy-select',
                'label' => 'Dummy select',
                'options' => [1 => 'option1', 2 => 'option2'],
                'multiple' => false,
                'display' => 'dropdown',
                'clearable' => false,
                'validationRules' => [],
                'helpMessage' => 'Select an option',
                'isUpdatable' => true,
                'order' => 1,
            ],
        ]),
        fn (Request $request) => $request->method() == 'DELETE',
    ]);
});

it('sends custom fields configuration to Ozu', function () {
    Schema::swap(new class
    {
        public function getColumnListing($table): array
        {
            return ['dummy-text'];
        }

        public function getColumnType($table, $column): string
        {
            return 'string';
        }
    });

    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy';
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn (Request $request) => $request['customFields'] == collect(['dummy-text' => 'string']),
        fn (Request $request) => $request->method() == 'DELETE',
    ]);
});

it('deletes pre-existing and unconfigured collections', function () {
    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy1';
            }
        },
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy2';
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn ($request) => $request->method() == 'POST',
        fn ($request) => $request->method() == 'POST',
        fn (Request $request) => $request
            ->url() == sprintf(
                '%s/api/%s/collections/configure',
                rtrim(config('ozu-client.api_host'), '/'),
                config('ozu-client.api_version'),
            )
            && $request->method() == 'DELETE'
            && $request->data()['except'] == ['dummy1', 'dummy2'],
    ]);
});

it('sends subcollections configuration to Ozu', function () {
    Http::fake();

    // Create a parent collection with subcollections
    $parentCollectionClass = new class extends DummyTestModel
    {
        public function ozuCollectionKey(): string
        {
            return 'dummy-parent';
        }

        public static function configureOzuCollection(OzuCollectionConfig $config): OzuCollectionConfig
        {
            return $config
                ->setLabel('Parent collection')
                ->addSubCollection(DummySubcollectionTestModel::class);
        }
    };

    config(['ozu-client.collections' => [$parentCollectionClass]]);

    $this->artisan('ozu:configure')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    // Assert parent is sent with subcollections in payload
    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'dummy-parent'
        ) && $request['subCollections']->count() === 1
            && $request['subCollections']->first() === 'dummy-subcollection';
    });

    // Assert subcollection is also processed and sent
    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'dummy-subcollection'
        );
    });
});

it('fails if an unconfigured field is defined in the list cms configuration', function () {
    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy';
            }

            public static function configureOzuCollectionList(OzuCollectionListConfig $config): OzuCollectionListConfig
            {
                return $config
                    ->addColumn(OzuColumn::makeText('dummy-text'))
                    ->addColumn(OzuColumn::makeCheck('dummy-another-check'));
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutputToContain('The keys [dummy-another-check] are defined either in the list or in the form but are not custom fields of the model')
        ->assertExitCode(Command::SUCCESS);
});

it('fails if an unconfigured field is defined in the form cms configuration', function () {
    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy';
            }

            public static function configureOzuCollectionForm(OzuCollectionFormConfig $config): OzuCollectionFormConfig
            {
                return $config
                    ->addCustomField(
                        OzuField::makeText('dummy-another-text')
                    );
            }
        },
    ]]);

    $this->artisan('ozu:configure')
        ->expectsOutputToContain('The keys [dummy-another-text] are defined either in the list or in the form but are not custom fields of the model')
        ->assertExitCode(Command::SUCCESS);
});
