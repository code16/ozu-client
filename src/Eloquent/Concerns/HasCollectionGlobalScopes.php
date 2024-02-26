<?php

namespace Code16\JockoClient\Eloquent\Concerns;

use Code16\JockoClient\JockoCms\JockoCollectionListConfig;
use Illuminate\Database\Eloquent\Builder;


trait HasCollectionGlobalScopes
{
    public static function bootHasCollectionGlobalScopes(): void
    {
        $list = static::configureJockoCollectionList(new JockoCollectionListConfig());

        static::addGlobalScope('order', function (Builder $query) use ($list) {
            if($list->isReorderable()) {
                $query->orderBy('order');
            }
        });
    }
}
