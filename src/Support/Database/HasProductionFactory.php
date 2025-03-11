<?php

namespace Code16\OzuClient\Support\Database;

trait HasProductionFactory
{
    public static function ozuProductionFactory(): self
    {
        return static::factory()->create([

        ]);
    }
}
