<?php

namespace Code16\OzuClient\OzuCms;

class OzuCollectionConfig
{
    protected string $label;
    protected string $icon;
    protected bool $hasPublicationState = false;

    protected ?string $autoDeployDateField = null;
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

    /**
     * Declare which date field will trigger auto-deploy when reached
     * @param string|null $field
     * @return $this
     */
    public function setHasAutoDeployDateField(?string $fieldKey = null): self
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

    public function hasAutoDeployDateField(): ?string
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
}
