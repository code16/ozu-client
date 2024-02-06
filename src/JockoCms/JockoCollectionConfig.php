<?php

namespace Code16\JockoClient\JockoCms;

class JockoCollectionConfig
{
    protected string $label;
    protected string $icon;
    protected bool $hasPublicationState = false;

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
}
