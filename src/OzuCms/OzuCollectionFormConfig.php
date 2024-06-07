<?php

namespace Code16\OzuClient\OzuCms;

use Code16\OzuClient\OzuCms\Form\OzuBelongsToField;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Illuminate\Support\Collection;

class OzuCollectionFormConfig
{
    protected array $fields = [];
    protected ?OzuBelongsToField $belongsToField = null;

    public function addCustomField(OzuField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function declareBelongsToField(string $ozuModelClass, string $label, bool $required = true): self
    {
        $ozuCollectionKey = app($ozuModelClass)->ozuCollectionKey();

        $this->belongsToField = (new OzuBelongsToField($ozuCollectionKey))
            ->setLabel($label)
            ->setClearable(!$required)
            ->setValidationRules($required ? ['required'] : []);

        return $this;
    }

    public function customFields(): Collection
    {
        return collect([
            $this->belongsToField,
            ...$this->fields
        ])->whereNotNull();
    }
}
