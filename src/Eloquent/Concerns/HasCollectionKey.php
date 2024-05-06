<?php

namespace Code16\OzuClient\Eloquent\Concerns;

use Code16\OzuClient\Eloquent\OzuModel;
use Illuminate\Database\Eloquent\Builder;

trait HasCollectionKey
{
    public static function bootHasCollectionKey(): void
    {
        static::creating(function (OzuModel $model) {
            $model->collection_key = $model->ozuCollectionKey();
        });

        static::addGlobalScope('collectionKey', function (Builder $query) {
            $query->where('collection_key', (new static)->ozuCollectionKey());
        });
    }

    public function ozuCollectionKey(): string
    {
        return str(class_basename(get_class($this)))
            ->snake()
            ->plural();
    }
}
