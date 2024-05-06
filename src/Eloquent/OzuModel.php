<?php

namespace Code16\OzuClient\Eloquent;

use Code16\OzuClient\Eloquent\Casts\OzuCustomAttribute;
use Code16\OzuClient\Eloquent\Concerns\HasCollectionGlobalScopes;
use Code16\OzuClient\Eloquent\Concerns\HasCollectionKey;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

abstract class OzuModel extends Model
{
    use HasCollectionKey;
    use HasCollectionGlobalScopes;

    protected $guarded = [];

    protected $table = 'posts';

    protected $casts = [
        'custom_properties' => 'array',
    ];

    protected array $ozuCustomAttributes = [];

    public function getMorphClass()
    {
        if (app()->environment('production')) {
            return 'post';
        }

        return parent::getMorphClass();
    }

    public function getCasts()
    {
        return array_merge(
            parent::getCasts(),
            collect($this->ozuCustomAttributes)
                ->mapWithKeys(fn($attribute) => [$attribute => OzuCustomAttribute::class])
                ->toArray()
        );
    }

    public function hasCustomAttribute(string $key): bool
    {
        return in_array($key, $this->ozuCustomAttributes);
    }

    public function cover(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('model_key', 'cover');
    }

    abstract public static function configureOzuCollection(OzuCollectionConfig $config): OzuCollectionConfig;

    abstract public static function configureOzuCollectionList(OzuCollectionListConfig $config): OzuCollectionListConfig;

    abstract public static function configureOzuCollectionForm(OzuCollectionFormConfig $config): OzuCollectionFormConfig;
}
