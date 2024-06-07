<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuBelongsToField extends OzuField
{
    protected bool $clearable = false;

    public function __construct(
        protected string $ozuCollectionKey
    ) {
        parent::__construct('parent_id');
    }

    public function type(): string
    {
        return 'belongsTo';
    }

    public function setClearable(bool $clearable = true): self
    {
        $this->clearable = $clearable;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'collectionKey' => $this->ozuCollectionKey,
            'clearable' => $this->clearable,
        ]);
    }
}

