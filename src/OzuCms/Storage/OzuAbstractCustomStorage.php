<?php

namespace Code16\OzuClient\OzuCms\Storage;

use Illuminate\Support\Collection;

abstract class OzuAbstractCustomStorage
{
    public static function make(): self
    {
        return new static;
    }

    abstract public function meetRequirements(): bool;

    abstract public function whatsMissing(): Collection;

    abstract public function toArray(): array;
}
