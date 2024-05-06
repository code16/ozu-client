<?php

namespace Code16\OzuClient\OzuCms;

class OzuCollectionConfig
{
    protected string $label;
    protected string $icon;
    protected bool $hasPublicationState = false;
    private bool $isCreatable = true;
    private bool $isDeletable = true;

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

    public function label(): string
    {
        return $this->label;
    }

    public function icon(): string
    {
        return $this->icon;
    }

    public function hasPublicationState(): bool
    {
        return $this->hasPublicationState;
    }

    public function isCreatable(): bool
    {
        return $this->isCreatable;
    }

    public function isDeletable(): bool
    {
        return $this->isDeletable;
    }
}
