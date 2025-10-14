<?php

namespace Code16\OzuClient\OzuCms;

use Illuminate\Support\Collection;

class OzuCollectionConfig
{
    protected string $label;
    protected string $icon;
    protected bool $hasPublicationState = false;
    protected ?string $autoDeployDateField = null;
    private bool $isCreatable = true;
    private bool $isDeletable = true;
    private array $subCollections = [];

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function setHasPublicationState(bool $hasState = true): self
    {
        $this->hasPublicationState = $hasState;

        return $this;
    }

    /**
     * Declare which date field will trigger auto-deploy when reached
     */
    public function setAutoDeployDateField(string $fieldKey): self
    {
        $this->autoDeployDateField = $fieldKey;

        return $this;
    }

    public function setIsCreatable(bool $isCreatable = true): self
    {
        $this->isCreatable = $isCreatable;

        return $this;
    }

    public function setIsDeletable(bool $isDeletable = true): self
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }

    public function addSubCollection(string $collectionClass): self
    {
        $this->subCollections[] = $collectionClass;

        return $this;
    }

    public function label(): string
    {
        return $this->label ?? 'no label';
    }

    public function icon(): ?string
    {
        return $this->icon ?? null;
    }

    public function hasPublicationState(): bool
    {
        return $this->hasPublicationState;
    }

    public function hasAutoDeployDateField(): bool
    {
        return !is_null($this->autoDeployDateField);
    }

    public function autoDeployDateField(): ?string
    {
        return $this->autoDeployDateField;
    }

    public function isCreatable(): bool
    {
        return $this->isCreatable;
    }

    public function isDeletable(): bool
    {
        return $this->isDeletable;
    }

    public function subCollections(): Collection
    {
        return collect($this->subCollections);
    }
}
