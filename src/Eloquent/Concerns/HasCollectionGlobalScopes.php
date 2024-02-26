<?php

namespace Code16\JockoClient\Eloquent\Concerns;

use Code16\JockoClient\Eloquent\JockoModel;
use Code16\JockoClient\Facades\Jocko;
use Code16\JockoClient\JockoCms\JockoCollectionListConfig;
use Illuminate\Database\Eloquent\Builder;


trait HasCollectionGlobalScopes
{
    public static function bootHasCollectionGlobalScopes(): void
    {
        $instance = new static;
        $list = static::configureJockoCollectionList(new JockoCollectionListConfig());

        static::addGlobalScope('collectionKey', function (Builder $query) use ($instance) {
            $query->where('collection_key', $instance->jockoCollectionKey());
        });

        static::addGlobalScope('order', function (Builder $query) use ($list) {
            if($list->isReorderable()) {
                $query->orderBy('order');
            }
        });
    }
}
