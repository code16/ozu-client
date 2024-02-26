<?php

namespace Code16\JockoClient\Eloquent\Concerns;

use Code16\JockoClient\Eloquent\JockoModel;
use Illuminate\Database\Eloquent\Builder;

trait HasCollectionKey
{
    public static function bootHasCollectionKey(): void
    {
        static::creating(function (JockoModel $model) {
            $model->collection_key = $model->jockoCollectionKey();
        });

        static::addGlobalScope('collectionKey', function (Builder $query) {
            $query->where('collection_key', (new static)->jockoCollectionKey());
        });
    }

    public function jockoCollectionKey(): string
    {
        return str(class_basename(get_class($this)))
            ->snake()
            ->plural();
    }
}
