<?php

namespace Code16\JockoClient\Eloquent\Concerns;

use Code16\JockoClient\Eloquent\JockoModel;
use Code16\JockoClient\Facades\Jocko;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin JockoModel
 */
trait HasCollectionGlobalScopes
{
    public static function bootHasCollectionGlobalScopes(): void
    {
        static::addGlobalScope('order', function (Builder $query) {
            $meta = Jocko::getCollectionMeta((new static)->jockoCollectionKey());

            if($meta['reorderable']) {
                $query->orderBy('order');
            }
        });
    }
}
