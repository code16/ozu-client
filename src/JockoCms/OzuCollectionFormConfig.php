<?php

namespace Code16\OzuClient\OzuCms;

use Code16\OzuClient\OzuCms\Form\OzuField;
use Illuminate\Support\Collection;

class OzuCollectionFormConfig
{
    protected array $fields = [];

    public function addCustomField(OzuField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function customFields(): Collection
    {
        return collect($this->fields);
    }
}
