<?php

namespace Code16\JockoClient\Eloquent;

use Code16\JockoClient\Eloquent\Casts\JockoCustomAttribute;
use Code16\JockoClient\JockoCms\JockoCollectionFormConfig;
use Code16\JockoClient\JockoCms\JockoCollectionListConfig;
use Code16\JockoClient\JockoCms\JockoCollectionConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

abstract class JockoModel extends Model
{
    protected $guarded = [];

    protected $table = 'posts';

    protected $casts = [
        'custom_properties' => 'array',
    ];

    protected array $jockoCustomAttributes = [];

    public function getMorphClass()
    {
        if (app()->environment('production')) {
            return 'jocko-post';
        }

        return parent::getMorphClass();
    }

    public function getCasts()
    {
        return array_merge(
            parent::getCasts(),
            collect($this->jockoCustomAttributes)
                ->mapWithKeys(fn($attribute) => [$attribute => JockoCustomAttribute::class])
                ->toArray()
        );
    }

    public function hasCustomAttribute(string $key): bool
    {
        return in_array($key, $this->jockoCustomAttributes);
    }

    public function jockoCollectionKey(): string
    {
        return str(class_basename(get_class($this)))
            ->snake()
            ->plural();
    }

    public function cover(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('model_key', 'cover');
    }

    abstract public static function configureJockoCollection(JockoCollectionConfig $config): JockoCollectionConfig;

    abstract public static function configureJockoCollectionList(JockoCollectionListConfig $config): JockoCollectionListConfig;

    abstract public static function configureJockoCollectionForm(JockoCollectionFormConfig $config): JockoCollectionFormConfig;
}
