<?php

namespace Code16\OzuClient\OzuCms\Storage;

use Illuminate\Support\Collection;

abstract class OzuAbstractCustomStorage
{
    public static function make(): self
    {
        return new static();
    }

    public abstract function meetRequirements(): bool;

    public abstract function whatsMissing(): Collection;

    public abstract function toArray(): array;
}
