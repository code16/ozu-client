<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuSelectField extends OzuField
{
    protected bool $multiple = false;
    protected string $display = 'list';
    protected bool $clearable = false;
    protected array $options = [];

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function setDisplayAsDropdown(): self
    {
        $this->display = 'dropdown';

        return $this;
    }

    public function setDisplayAsList(): self
    {
        $this->display = 'list';

        return $this;
    }

    public function setMultiple(bool $multiple = true): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function setClearable(bool $clearable = true): self
    {
        $this->clearable = $clearable;

        return $this;
    }

    public function type(): string
    {
        return 'select';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'multiple' => $this->multiple,
            'display' => $this->display,
            'clearable' => $this->clearable,
            'options' => $this->options,
        ]);
    }
}
