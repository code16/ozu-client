<?php

namespace Code16\OzuClient\Eloquent\Concerns;

use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Illuminate\Database\Eloquent\Builder;


trait HasCollectionGlobalScopes
{
    public static function bootHasCollectionGlobalScopes(): void
    {
        $list = static::configureOzuCollectionList(new OzuCollectionListConfig());

        static::addGlobalScope('order', function (Builder $query) use ($list) {
            if($list->isReorderable()) {
                $query->orderBy('order');
            }
        });
    }
}
