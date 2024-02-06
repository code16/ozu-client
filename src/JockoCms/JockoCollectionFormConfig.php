<?php

namespace Code16\JockoClient\JockoCms;

use Code16\JockoClient\JockoCms\Form\JockoField;
use Illuminate\Support\Collection;

class JockoCollectionFormConfig
{
    protected array $fields = [];

    public function addCustomField(JockoField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function customFields(): Collection
    {
        return collect($this->fields);
    }
}
