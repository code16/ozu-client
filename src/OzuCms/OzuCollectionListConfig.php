<?php

namespace Code16\OzuClient\OzuCms;

use Code16\OzuClient\OzuCms\List\OzuColumn;
use Illuminate\Support\Collection;

class OzuCollectionListConfig
{
    protected bool $isReorderable = false;
    protected bool $isSearchable = false;
    protected bool $isPaginated = false;
    protected array $columns = [];

    public function setIsReorderable(bool $isReorderable = true): self
    {
        $this->isReorderable = $isReorderable;

        return $this;
    }

    public function setIsSearchable(bool $isSearchable = true): self
    {
        $this->isSearchable = $isSearchable;

        return $this;
    }

    public function setIsPaginated(bool $isPaginated = true): self
    {
        $this->isPaginated = $isPaginated;

        return $this;
    }

    public function addColumn(OzuColumn $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    public function isReorderable(): bool
    {
        return $this->isReorderable;
    }

    public function isSearchable(): bool
    {
        return $this->isSearchable;
    }

    public function isPaginated(): bool
    {
        return $this->isPaginated;
    }

    public function columns(): Collection
    {
        return collect($this->columns);
    }
}
