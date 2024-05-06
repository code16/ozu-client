<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuDateField extends OzuField
{
    protected bool $hasTime = false;

    public function setHasTime(bool $hasTime = true): self
    {
        $this->hasTime = $hasTime;

        return $this;
    }

    public function type(): string
    {
        return 'date';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'hasTime' => $this->hasTime,
        ]);
    }
}
