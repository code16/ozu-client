<?php

use Code16\OzuClient\OzuCms\List\OzuColumn;

it('sets default text values',  function() {
   $ozuColumn = OzuColumn::makeText('key', 1);

   expect($ozuColumn->type())->toBe("text")
       ->and($ozuColumn->key())->toBe("key")
       ->and($ozuColumn->size())->toBe(1)
       ->and($ozuColumn->label())->toBeNull();
});

it('allows to use setLabel and setDefaultSort',  function() {
    $ozuColumn = OzuColumn::makeText('key', 1)
        ->setLabel('label')
        ->setDefaultSort('desc');

    expect($ozuColumn->label())->toBe("label")
        ->and($ozuColumn->getDefaultSortDirection())->toBe("desc");
});

it('allows to use setDefaultSort with default value',  function() {
    $ozuColumn = OzuColumn::makeText('key', 1)
        ->setDefaultSort();

    expect($ozuColumn->getDefaultSortDirection())->toBe("asc");
});

it('sets default check values',  function() {
    $ozuColumn = OzuColumn::makeCheck('key', 1);

    expect($ozuColumn->type())->toBe("check")
        ->and($ozuColumn->key())->toBe("key")
        ->and($ozuColumn->size())->toBe(1)
        ->and($ozuColumn->label())->toBeNull();
});

it('sets default date values',  function() {
    $ozuColumn = OzuColumn::makeDate('key', 1);

    expect($ozuColumn->type())->toBe("date")
        ->and($ozuColumn->key())->toBe("key")
        ->and($ozuColumn->size())->toBe(1)
        ->and($ozuColumn->label())->toBeNull();
});

it('sets default image values',  function() {
    $ozuColumn = OzuColumn::makeImage('key', 1);

    expect($ozuColumn->type())->toBe("image")
        ->and($ozuColumn->key())->toBe("key")
        ->and($ozuColumn->size())->toBe(1)
        ->and($ozuColumn->label())->toBeNull();
});


