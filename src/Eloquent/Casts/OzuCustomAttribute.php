<?php

namespace Code16\OzuClient\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class OzuCustomAttribute implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $model->custom_properties[$key] ?? null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $properties = $model->custom_properties ?? [];
        $properties[$key] = $value;
        $model->custom_properties = $properties;

        return [
            'custom_properties' => json_encode($properties),
        ];
    }
}
