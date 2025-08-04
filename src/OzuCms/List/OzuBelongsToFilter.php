<?php

namespace Code16\OzuClient\OzuCms\List;

class OzuBelongsToFilter
{
    protected bool $required = true;

    private ?string $label = null;

    public function __construct(protected string $ozuCollectionKey) {}

    public function setRequired(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'collectionKey' => $this->ozuCollectionKey,
            'label' => $this->label,
            'required' => $this->required,
        ];
    }
}
