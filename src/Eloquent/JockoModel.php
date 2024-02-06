<?php

namespace Code16\JockoClient\Eloquent;

use Code16\JockoClient\JockoCms\JockoCollectionFormConfig;
use Code16\JockoClient\JockoCms\JockoCollectionListConfig;
use Code16\JockoClient\JockoCms\JockoCollectionConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

abstract class JockoModel extends Model
{
    protected $guarded = [];
    protected $table = 'posts';

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
