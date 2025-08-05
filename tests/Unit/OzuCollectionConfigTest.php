<?php

use Code16\OzuClient\OzuCms\OzuCollectionConfig;

it('set defauls config values', function () {
    $ozuCollectionConfig = new OzuCollectionConfig();

    expect($ozuCollectionConfig->label())->toBe('no label')->and($ozuCollectionConfig->icon())->toBeNull()
        ->and($ozuCollectionConfig->hasPublicationState())->toBeFalse()
        ->and($ozuCollectionConfig->autoDeployDateField())->toBeNull()
        ->and($ozuCollectionConfig->isCreatable())->toBeTrue()
        ->and($ozuCollectionConfig->isDeletable())->toBeTrue()
        ->and($ozuCollectionConfig)->toHaveProperties([
            'label',
            'icon',
            'hasPublicationState',
            'autoDeployDateField',
            'isCreatable',
            'isDeletable',
        ])
        ->and($ozuCollectionConfig::class)->toHaveMethods([
            'setLabel',
            'setIcon',
            'setHasPublicationState',
            'setAutoDeployDateField',
            'setIsCreatable',
            'setIsDeletable',
            'label',
            'icon',
            'hasPublicationState',
            'autoDeployDateField',
            'hasAutoDeployDateField',
            'isCreatable',
            'isDeletable',
        ]);

});

it('allows to set label and icon', function () {
    $ozuCollectionConfig = new OzuCollectionConfig();

    $ozuCollectionConfig
        ->setLabel('label')
        ->setIcon('icon');

    expect($ozuCollectionConfig->label())->toBe('label')
        ->and($ozuCollectionConfig->icon())->toBe('icon');
});

it('allows to set hasPublicationState and setAutoDeployField', function () {
    $ozuCollectionConfig = new OzuCollectionConfig();

    $ozuCollectionConfig->setHasPublicationState();
    $ozuCollectionConfig->setAutoDeployDateField('date');

    expect($ozuCollectionConfig->hasPublicationState())->toBeTrue()
        ->and($ozuCollectionConfig->autoDeployDateField())->toBe('date');
});

it('allows to set isCreatable and isDeletable', function () {
    $ozuCollectionConfig = new OzuCollectionConfig();

    $ozuCollectionConfig->setIsCreatable(false);
    $ozuCollectionConfig->setIsDeletable(false);

    expect($ozuCollectionConfig->isCreatable())->toBeFalse()
        ->and($ozuCollectionConfig->isDeletable())->toBeFalse();
});

it('allows to chain methods', function () {
    $ozuCollectionConfig = new OzuCollectionConfig();

    $ozuCollectionConfig
        ->setLabel('label')
        ->setIcon('icon')
        ->setHasPublicationState()
        ->setAutoDeployDateField('date')
        ->setIsCreatable(false)
        ->setIsDeletable(false);

    expect($ozuCollectionConfig->label())->toBe('label')
        ->and($ozuCollectionConfig->icon())->toBe('icon')
        ->and($ozuCollectionConfig->hasPublicationState())->toBeTrue()
        ->and($ozuCollectionConfig->autoDeployDateField())->toBe('date')
        ->and($ozuCollectionConfig->isCreatable())->toBeFalse()
        ->and($ozuCollectionConfig->isDeletable())->toBeFalse();
});
