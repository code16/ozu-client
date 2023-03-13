<?php

namespace Code16\JockoClient\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait CastsCollection
{
    protected function castCollection(array $items): array
    {
        return collect($items)
            ->map(fn ($attributes) => [
                ...collect($attributes)
                    ->mapWithKeys(fn ($value, $key) => [
                        $key => is_array($value) ? json_encode($value) : $value
                    ])
            ])
            ->toArray();
    }
}
