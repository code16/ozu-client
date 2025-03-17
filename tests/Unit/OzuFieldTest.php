<?php

use Code16\OzuClient\OzuCms\Form\OzuEditorToolbarEnum;
use Code16\OzuClient\OzuCms\Form\OzuField;

it('allows to create a text field', function () {
    $field = OzuField::makeText('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false);

    expect($field->toArray())->toEqual([
        'type' => 'text',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
    ]);
});

it('allows to create a select field', function () {
    $field = OzuField::makeSelect('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false)
        ->setOptions([
            ['1' => 'One'],
            ['2' => 'Two'],
        ])
        ->setMultiple();

    $expected = [
        'type' => 'select',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'multiple' => true,
        'display' => 'list',
        'clearable' => false,
        'options' => [
            ['1' => 'One'],
            ['2' => 'Two'],
        ],
    ];

    expect($field->toArray())->toEqual($expected);

    $field
        ->setDisplayAsDropdown()
        ->setClearable();

    expect($field->toArray())->toEqual(array_merge($expected, [
        'display' => 'dropdown',
        'clearable' => true,
    ]));
});

it('allows to create a check field', function () {
    $field = OzuField::makeCheck('key', 'text')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false);

    expect($field->toArray())->toEqual([
        'type' => 'check',
        'key' => 'key',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'text' => 'text',
        'label' => null,
    ]);
});

it('allows to create an editor field', function () {
    $field = OzuField::makeEditor('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false);

    expect($field->toArray())->toEqual([
        'type' => 'editor',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'withoutParagraphs' => false,
        'hideToolbar' => false,
        'toolbar' => [
            OzuEditorToolbarEnum::Bold->value,
            OzuEditorToolbarEnum::Italic->value,
            OzuEditorToolbarEnum::Separator->value,
            OzuEditorToolbarEnum::BulletList->value,
            OzuEditorToolbarEnum::Link->value,
        ],
        'height' => 200,
        'maxHeight' => null,
    ]);
});

it('allows to use setToolbar, setWithoutParagraph and hideToolbar on an editor field', function () {
    $field = OzuField::makeEditor('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false)
        ->setWithoutParagraphs()
        ->setToolbar([
            OzuEditorToolbarEnum::Bold,
            OzuEditorToolbarEnum::Italic,
        ]);

    expect($field->toArray())->toEqual([
        'type' => 'editor',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'withoutParagraphs' => true,
        'hideToolbar' => false,
        'toolbar' => [
            OzuEditorToolbarEnum::Bold->value,
            OzuEditorToolbarEnum::Italic->value,
        ],
        'height' => 200,
        'maxHeight' => null,
    ]);

    $field->hideToolbar();

    expect($field->toArray())->toEqual(array_merge($field->toArray(), [
        'hideToolbar' => true,
    ]));
});

it('allows to set height and maxHeight on an editor field', function () {
    $field = OzuField::makeEditor('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false)
        ->setHeight(300, 500);

    expect($field->toArray())->toEqual([
        'type' => 'editor',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'withoutParagraphs' => false,
        'hideToolbar' => false,
        'toolbar' => [
            OzuEditorToolbarEnum::Bold->value,
            OzuEditorToolbarEnum::Italic->value,
            OzuEditorToolbarEnum::Separator->value,
            OzuEditorToolbarEnum::BulletList->value,
            OzuEditorToolbarEnum::Link->value,
        ],
        'height' => 300,
        'maxHeight' => 500,
    ]);
});

it('allows to create an image field', function () {
    $field = OzuField::makeImage('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false);

    expect($field->toArray())->toEqual([
        'type' => 'image',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasLegend' => false,
        'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'maxFileSize' => 5,
        'cropRatio' => null,
    ]);
});

it('allows to set hasLegend, allowedExtensions, maxFileSize and cropRatio on an image field', function () {
    $field = OzuField::makeImage('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false)
        ->setHasLegend()
        ->setAllowedExtensions(['pdf'])
        ->setMaxFileSizeInMB(10)
        ->setCropRatio('16:9');

    expect($field->toArray())->toEqual([
        'type' => 'image',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasLegend' => true,
        'allowedExtensions' => ['pdf'],
        'maxFileSize' => 10,
        'cropRatio' => '16:9',
    ]);
});

it('allows to create an image list field', function () {
    $field = OzuField::makeImageList('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false);

    expect($field->toArray())->toEqual([
        'type' => 'imageList',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasLegend' => false,
        'maxFileSize' => 5,
        'cropRatio' => null,
        'maxItems' => 10,
    ]);
});

it('allows to set hasLegend, maxFileSize, cropRatio and maxItems on an image list field', function () {
    $field = OzuField::makeImageList('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false)
        ->setHasLegend()
        ->setMaxFileSize(10)
        ->setCropRatio('16:9')
        ->setMaxItems(5);

    expect($field->toArray())->toEqual([
        'type' => 'imageList',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasLegend' => true,
        'maxFileSize' => 10,
        'cropRatio' => '16:9',
        'maxItems' => 5,
    ]);
});

it('allows to create a file list field', function () {
    $field = OzuField::makeFileList('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false);

    expect($field->toArray())->toEqual([
        'type' => 'fileList',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasLegend' => false,
        'maxFileSize' => 5,
        'maxItems' => 10,
    ]);
});

it('allows to set hasLegend, maxFileSize and maxItems on a file list field', function () {
    $field = OzuField::makeFileList('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false)
        ->setHasLegend()
        ->setMaxFileSize(10)
        ->setMaxItems(4);

    expect($field->toArray())->toEqual([
        'type' => 'fileList',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasLegend' => true,
        'maxFileSize' => 10,
        'maxItems' => 4,
    ]);
});

it('allows to create a date field', function () {
    $field = OzuField::makeDate('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false);

    expect($field->toArray())->toEqual([
        'type' => 'date',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasTime' => false,
    ]);
});

it('allows to set hasTime on a date field', function () {
    $field = OzuField::makeDate('key')
        ->setLabel('label')
        ->setHelpMessage('help')
        ->setValidationRules(['required'])
        ->setIsUpdatable(false)
        ->setHasTime();

    expect($field->toArray())->toEqual([
        'type' => 'date',
        'key' => 'key',
        'label' => 'label',
        'validationRules' => ['required'],
        'helpMessage' => 'help',
        'isUpdatable' => false,
        'hasTime' => true,
    ]);
});
