<?php

use Code16\OzuClient\Eloquent\Media;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    (include __DIR__.'/../../../database/migrations/create_ozu_tables.php')->up();
    Media::flushColumnCache();
});

it('stores real columns as attributes and other keys as custom properties', function () {
    $media = Media::create([
        'model_type' => 'project',
        'model_id' => 1,
        'model_key' => 'cover',
        'file_name' => 'data/medias/cover.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'local',
        'filters' => ['crop' => true],
        'alt' => 'A cover',
    ]);

    $media = Media::find($media->id);

    expect($media->file_name)->toBe('data/medias/cover.jpg')
        ->and($media->getAttributes())->toHaveKey('file_name')
        ->and($media->getAttributes())->not->toHaveKey('alt')
        ->and($media->alt)->toBe('A cover')
        ->and($media->filters)->toBe(['crop' => true])
        ->and($media->custom_properties)->toBe(['filters' => ['crop' => true], 'alt' => 'A cover'])
        ->and($media->unknown)->toBeNull();
});

it('queries the schema at most once when reading several attributes', function () {
    $media = Media::create([
        'model_type' => 'project',
        'model_id' => 1,
        'file_name' => 'data/medias/cover.jpg',
        'mime_type' => 'image/jpeg',
        'filters' => ['crop' => true],
    ]);
    $media = Media::find($media->id);
    Media::flushColumnCache();

    $queries = 0;
    DB::listen(function (QueryExecuted $query) use (&$queries) {
        $queries++;
    });

    // A single column listing may take more than one query depending on the driver (2 on SQLite)
    Schema::getColumnListing('medias');
    $queriesPerListing = $queries;
    $queries = 0;

    $readAttributes = function () use ($media) {
        $media->disk;
        $media->file_name;
        $media->mime_type;
        $media->filters;
        $media->custom_properties;
    };

    $readAttributes();
    expect($queries)->toBeLessThanOrEqual($queriesPerListing);

    $queries = 0;
    $readAttributes();
    expect($queries)->toBe(0);
});
