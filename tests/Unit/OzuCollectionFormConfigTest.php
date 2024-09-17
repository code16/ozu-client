<?php

use Code16\OzuClient\OzuCms\Form\OzuEditorField;
use Code16\OzuClient\OzuCms\Form\OzuEditorToolbarEnum;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\Form\OzuImageField;
use Code16\OzuClient\OzuCms\Form\OzuTextField;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;

it("set default form values", function() {
    $ozuCollectionFormConfig = new OzuCollectionFormConfig();


    expect($ozuCollectionFormConfig->customFields())->toBeEmpty()
        ->and($ozuCollectionFormConfig->titleField())->toBeInstanceOf(OzuTextField::class)
        ->and($ozuCollectionFormConfig->titleField())->toHaveProperty('key')
        ->and($ozuCollectionFormConfig->coverField())->toBeInstanceOf(OzuImageField::class)
        ->and($ozuCollectionFormConfig->coverField())->toHaveProperties(['maxFileSizeInMB', 'hasLegend', 'cropRatio', 'allowedExtensions'])
        ->and($ozuCollectionFormConfig->contentField())->toBeInstanceOf(OzuEditorField::class)
        ->and($ozuCollectionFormConfig->contentField())->toHaveProperties(['withoutParagraphs', 'hideToolbar', 'toolbar', 'height', 'maxHeight'])
        ->and($ozuCollectionFormConfig)->toHaveProperties([
            'titleField',
            'hideTitleField',
            'coverField',
            'hideCoverField',
            'contentField',
            'hideContentField',
            'fields',
            'belongsToField'
        ])
        ->and($ozuCollectionFormConfig)->toHaveMethods([
            'addCustomField',
            'configureTitleField',
            'hideTitleField',
            'configureCoverField',
            'hideCoverField',
            'configureContentField',
            'hideContentField',
            'declareBelongsToField',
            'customFields',
            'titleField',
            'coverField',
            'contentField'
        ]);
});

it("allows to add custom fields", function() {
    $ozuCollectionFormConfig = new OzuCollectionFormConfig();

    $ozuCollectionFormConfig
        ->addCustomField(OzuField::makeText('text'))
        ->addCustomField(OzuField::makeImage('image'));

    expect($ozuCollectionFormConfig->customFields())->toHaveCount(2)
        ->and($ozuCollectionFormConfig->customFields()[0])->toBeInstanceOf(OzuTextField::class)
        ->and($ozuCollectionFormConfig->customFields()[0]?->toArray()['key'])->toBe('text')
        ->and($ozuCollectionFormConfig->customFields()[1])->toBeInstanceOf(OzuImageField::class)
        ->and($ozuCollectionFormConfig->customFields()[1]?->toArray()['key'])->toBe('image');
});

it("allows to configure title field", function() {
    $ozuCollectionFormConfig = new OzuCollectionFormConfig();

    $ozuCollectionFormConfig
        ->configureTitleField(function($field) {
            $field->setLabel('new label');
        });

    expect($ozuCollectionFormConfig->titleField()->toArray()['label'])->toBe('new label');
});

it("allows to configure cover field", function() {
    $ozuCollectionFormConfig = new OzuCollectionFormConfig();

    $ozuCollectionFormConfig
        ->configureCoverField(function($field) {
            $field->setMaxFileSizeInMB(50)
                ->setHasLegend();
        });

    expect($ozuCollectionFormConfig->coverField()->toArray()['maxFileSize'])->toBe(50)
        ->and($ozuCollectionFormConfig->coverField()->toArray()['hasLegend'])->toBeTrue();
});

it("allows to configure content field", function() {
    $ozuCollectionFormConfig = new OzuCollectionFormConfig();

    $ozuCollectionFormConfig
        ->configureContentField(function($field) {
            $field->setHeight(100, 200)
                ->setToolbar([
                    OzuEditorToolbarEnum::Bold,
                    OzuEditorToolbarEnum::Italic,
                    OzuEditorToolbarEnum::BulletList,
                ]);
        });

    expect($ozuCollectionFormConfig->contentField()->toArray()['height'])->toBe(100)
        ->and($ozuCollectionFormConfig->contentField()->toArray()['maxHeight'])->toBe(200)
        ->and($ozuCollectionFormConfig->contentField()->toArray()['toolbar'])->toBe([
            OzuEditorToolbarEnum::Bold->value,
            OzuEditorToolbarEnum::Italic->value,
            OzuEditorToolbarEnum::BulletList->value,
        ]);

});

it("allows to hide title, cover and content fields", function() {
    $ozuCollectionFormConfig = new OzuCollectionFormConfig();

    $ozuCollectionFormConfig
        ->hideTitleField()
        ->hideCoverField()
        ->hideContentField();

    expect($ozuCollectionFormConfig->titleField())->toBeNull()
        ->and($ozuCollectionFormConfig->coverField())->toBeNull()
        ->and($ozuCollectionFormConfig->contentField())->toBeNull();
});

