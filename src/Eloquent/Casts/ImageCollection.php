<?php

namespace Code16\JockoClient\Eloquent\Casts;

use Code16\JockoClient\Support\Image;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ImageCollection implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return Collection<Image>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        return collect($model->fromJson($value))->mapInto(Image::class);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        return $value;
    }
}
