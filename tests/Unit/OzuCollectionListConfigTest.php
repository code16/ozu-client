<?php

use Code16\OzuClient\OzuCms\List\OzuBelongsToFilter;
use Code16\OzuClient\OzuCms\List\OzuColumn;
use Code16\OzuClient\OzuCms\List\OzuTextColumn;
use Code16\OzuClient\OzuCms\List\OzuThumbnailColumn;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Code16\OzuClient\Tests\Fixtures\DummyTestModel;

it('set default config values', function () {
    expect(new OzuCollectionListConfig())
        ->isReorderable()->toBeFalse()
        ->isPaginated()->toBeFalse()
        ->isSearchable()->toBeFalse()
        ->columns()->toBeEmpty()
        ->defaultSort()->toBeNull()
        ->and(new OzuCollectionListConfig())
        ->toHaveProperties([
            'isReorderable',
            'isSearchable',
            'isPaginated',
            'belongsToFilter',
            'columns',
        ])
        ->and(OzuCollectionListConfig::class)
        ->toHaveMethods([
            'columns',
            'defaultSort',
            'belongsToFilter',
            'isReorderable',
            'isSearchable',
            'isPaginated',
            'addColumn',
            'declareBelongsToFilter',
            'setIsReorderable',
            'setIsSearchable',
            'setIsPaginated',
        ]);
});

it('allows to set isReorderable, isSearchable and isPaginated', function () {
    $ozuCollectionListConfig = new OzuCollectionListConfig();

    $ozuCollectionListConfig
        ->setIsReorderable()
        ->setIsSearchable()
        ->setIsPaginated();

    expect($ozuCollectionListConfig)->isReorderable()->toBeTrue()
        ->isSearchable()->toBeTrue()
        ->isPaginated()->toBeTrue();
});

it('allows to declare belongsToFilter', function () {
    $ozuCollectionListConfig = new OzuCollectionListConfig();

    $ozuCollectionListConfig->declareBelongsToFilter(DummyTestModel::class, 'label');

    expect($ozuCollectionListConfig->belongsToFilter())->toBeInstanceOf(OzuBelongsToFilter::class)
        ->and($ozuCollectionListConfig->belongsToFilter()->toArray()['collectionKey'])->toBe('dummy_test_models')
        ->and($ozuCollectionListConfig->belongsToFilter()->toArray()['label'])->toBe('label')
        ->and($ozuCollectionListConfig->belongsToFilter()->toArray()['required'])->toBeTrue();
});

it('allows to add columns', function () {
    $ozuCollectionListConfig = new OzuCollectionListConfig();

    $ozuCollectionListConfig
        ->addColumn(OzuColumn::makeText('text', 1))
        ->addColumn(OzuColumn::makeImage('image', 3));

    expect($ozuCollectionListConfig->columns())->toHaveCount(2)
        ->and($ozuCollectionListConfig->columns()[0])->toBeInstanceOf(OzuTextColumn::class)
        ->and($ozuCollectionListConfig->columns()[0]->key())->toBe('text')
        ->and($ozuCollectionListConfig->columns()[0]->size())->toBe(1)
        ->and($ozuCollectionListConfig->columns()[1])->toBeInstanceOf(OzuThumbnailColumn::class)
        ->and($ozuCollectionListConfig->columns()[1]->key())->toBe('image')
        ->and($ozuCollectionListConfig->columns()[1]->size())->toBe(3);
});

it('allows to set defaultSort', function () {
    $ozuCollectionListConfig = new OzuCollectionListConfig();

    $ozuCollectionListConfig
        ->addColumn(OzuColumn::makeText('text', 1)->setDefaultSort('desc'));

    expect($ozuCollectionListConfig->defaultSort())->toBe([
        'column' => 'text',
        'direction' => 'desc',
    ]);
});
