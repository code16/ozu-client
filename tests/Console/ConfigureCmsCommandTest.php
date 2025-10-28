<?php

use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\List\OzuColumn;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Code16\OzuClient\Tests\Fixtures\DummyTestModel;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Request;

beforeEach(function () {
    config(['ozu-client.website_key' => 'test']);
    Http::fake();
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

    $this->artisan('ozu:configure-cms')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'test',
            'dummy1'
        );
    });

    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'test',
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

    $this->artisan('ozu:configure-cms')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(function (Request $request) {
        return $request->url() == sprintf(
            '%s/api/%s/%s/collections/%s/configure',
            rtrim(config('ozu-client.api_host'), '/'),
            config('ozu-client.api_version'),
            'test',
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

    $this->artisan('ozu:configure-cms')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn (Request $request) => $request['list']['isReorderable'] == true
            && $request['list']['isSearchable'] == true
            && $request['list']['isPaginated'] == true
            && $request['list']['defaultSort'] == ['column' => 'dummy-date', 'direction' => 'asc']
            && $request['list']['columns'] == collect([
                [
                    'type' => 'text',
                    'key' => 'dummy-text',
                    'label' => 'Dummy text',
                    'size' => 1,
                ],
                [
                    'type' => 'check',
                    'key' => 'dummy-check',
                    'label' => 'Dummy check',
                    'size' => 2,
                ],
                [
                    'type' => 'image',
                    'key' => 'dummy-image',
                    'label' => 'Dummy image',
                    'size' => 3,
                ],
                [
                    'type' => 'date',
                    'key' => 'dummy-date',
                    'label' => 'Dummy date',
                    'size' => 3,
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

    $this->artisan('ozu:configure-cms')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn (Request $request) => $request['form']['fields'] == collect([
            [
                'type' => 'text',
                'key' => 'dummy-text',
                'label' => 'Dummy text',
                'validationRules' => ['required'],
                'helpMessage' => null,
                'isUpdatable' => true,
            ],
            [
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
            ],
        ]),
        fn (Request $request) => $request->method() == 'DELETE',
    ]);
});

it('sends custom fields configuration to Ozu', function () {
    Schema::partialMock()
        ->shouldReceive(
            'getColumnListing',
            'getColumnType'
        )
        ->andReturn(
            [
                ...DummyTestModel::$ozuColumns,
                'dummy_text',
            ],
            'text'
        );

    config(['ozu-client.collections' => [
        new class extends DummyTestModel
        {
            public function ozuCollectionKey(): string
            {
                return 'dummy';
            }
        },
    ]]);

    $this->artisan('ozu:configure-cms')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn (Request $request) => $request['customFields'] == collect(['dummy_text' => 'string']),
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

    $this->artisan('ozu:configure-cms')
        ->expectsOutput('CMS configuration sent to Ozu.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSentInOrder([
        fn ($request) => $request->method() == 'POST',
        fn ($request) => $request->method() == 'POST',
        fn (Request $request) => $request
                ->url() == sprintf(
                    '%s/api/%s/%s/collections/configure',
                    rtrim(config('ozu-client.api_host'), '/'),
                    config('ozu-client.api_version'),
                    'test',
                )
            && $request->method() == 'DELETE'
            && $request->data()['except'] == ['dummy1','dummy2']
        ]);
});

