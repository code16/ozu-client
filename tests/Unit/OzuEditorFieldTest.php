<?php

use Code16\OzuClient\Exceptions\OzuClientException;
use Code16\OzuClient\OzuCms\Form\OzuEditorToolbarButton;
use Code16\OzuClient\OzuCms\Form\OzuField;

it('throws when setting allowed extensions if File upload is not enabled', function () {
    $field = OzuField::makeEditor('content');

    // Default toolbar does not include File
    expect(fn () => $field->setAllowedExtensions(['jpg', 'pdf']))
        ->toThrow(OzuClientException::class);
});

it('normalizes allowed extensions when File upload is enabled', function () {
    $field = OzuField::makeEditor('content')
        ->setToolbar([
            OzuEditorToolbarButton::Bold,
            OzuEditorToolbarButton::File, // enable uploads
        ]);

    $field->setAllowedExtensions([' jpg ', 'PDF', '.zip']);

    expect($field->toArray()['allowedExtensions'])
        ->toBe(['.jpg', '.PDF', '.zip']);
});

it('includes allowedExtensions in payload only when File upload is enabled', function () {
    $field = OzuField::makeEditor('content');

    // By default, no File button -> no allowedExtensions key
    expect($field->toArray())
        ->not->toHaveKey('allowedExtensions');

    $field
        ->setToolbar([OzuEditorToolbarButton::File])
        ->setAllowedExtensions(['.jpg', '.pdf']);

    expect($field->toArray())
        ->toHaveKey('allowedExtensions')
        ->and($field->toArray()['allowedExtensions'])
        ->toBe([
            '.jpg', '.pdf',
        ]);
});

it('enforces max file size can only be set when Image or File upload is enabled', function () {
    $field = OzuField::makeEditor('content');

    // Neither Image nor File -> should throw
    expect(fn () => $field->setMaxFileSize(10))
        ->toThrow(OzuClientException::class);

    // With File -> should work
    $fieldWithFile = OzuField::makeEditor('content')
        ->setToolbar([OzuEditorToolbarButton::File])
        ->setAllowedExtensions(['.jpg', '.jpeg', '.png', '.pdf']);

    $fieldWithFile->setMaxFileSize(12);
    expect($fieldWithFile->toArray()['maxFileSize'])->toBe(12);

    // With Image -> should also work
    $fieldWithImage = OzuField::makeEditor('content')
        ->setToolbar([OzuEditorToolbarButton::Image]);
    $fieldWithImage->setMaxFileSize(8);
    expect($fieldWithImage->toArray()['maxFileSize'])->toBe(8);
});

it('enforces crop ratio can only be set when Image upload is enabled', function () {
    $field = OzuField::makeEditor('content');
    expect(fn () => $field->setCropRatio('16/9'))
        ->toThrow(OzuClientException::class);

    $fieldWithImage = OzuField::makeEditor('content')
        ->setToolbar([OzuEditorToolbarButton::Image])
        ->setCropRatio('4/3');

    expect($fieldWithImage->toArray()['cropRatio'])->toBe('4/3');
});
