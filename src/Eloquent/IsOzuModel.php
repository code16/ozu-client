<?php

namespace Code16\OzuClient\Eloquent;

use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/** @mixin Model */
trait IsOzuModel
{
    use HasSlug;

    public static array $ozuColumns = [
        'id',
        'title',
        'content',
        'slug',
        'order',
        'created_at',
        'updated_at',
    ];

    public function cover(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('model_key', 'cover');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function ozuCollectionKey(): string
    {
        return $this->getTable();
    }

    abstract public static function configureOzuCollection(OzuCollectionConfig $config): OzuCollectionConfig;

    abstract public static function configureOzuCollectionList(OzuCollectionListConfig $config): OzuCollectionListConfig;

    abstract public static function configureOzuCollectionForm(OzuCollectionFormConfig $config): OzuCollectionFormConfig;
}
