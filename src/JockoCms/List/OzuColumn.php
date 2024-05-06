<?php

namespace Code16\OzuClient\OzuCms\List;

abstract class OzuColumn
{
    protected ?string $label = null;
    protected bool $isDefaultSort = false;
    protected string $sortDirection = 'asc';

    protected function __construct(protected string $key, protected int $size)
    {
    }

    public static function makeText(string $key, int $size): OzuTextColumn
    {
        return new OzuTextColumn($key, $size);
    }

    public static function makeDate(string $key, int $size): OzuDateColumn
    {
        return new OzuDateColumn($key, $size);
    }

    public static function makeImage(string $key, int $size): OzuThumbnailColumn
    {
        return new OzuThumbnailColumn($key, $size);
    }

    public static function makeCheck(string $key, int $size): OzuCheckColumn
    {
        return new OzuCheckColumn($key, $size);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function size(): int
    {
        return $this->size;
    }

    abstract public function type(): string;

    public function label(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function setDefaultSort(string $direction = 'asc'): self
    {
        $this->isDefaultSort = true;
        $this->sortDirection = $direction;

        return $this;
    }

    public function isDefaultSort(): bool
    {
        return $this->isDefaultSort;
    }

    public function getDefaultSortDirection(): string
    {
        return $this->sortDirection;
    }
}
