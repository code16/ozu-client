<?php

namespace Code16\OzuClient\OzuCms;

use Closure;
use Code16\OzuClient\OzuCms\Form\OzuBelongsToField;
use Code16\OzuClient\OzuCms\Form\OzuCheckField;
use Code16\OzuClient\OzuCms\Form\OzuDateField;
use Code16\OzuClient\OzuCms\Form\OzuEditorField;
use Code16\OzuClient\OzuCms\Form\OzuEditorToolbarButton;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\Form\OzuImageField;
use Code16\OzuClient\OzuCms\Form\OzuSelectField;
use Code16\OzuClient\OzuCms\Form\OzuTextField;
use Illuminate\Support\Collection;

class OzuSettingsFormConfig
{
    protected array $fields = [];

    public function addSettingField(
        OzuTextField
        |OzuEditorField
        |OzuCheckField
        |OzuSelectField
        |OzuDateField $field
    ): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function fields(): Collection
    {
        return collect($this->fields)
            ->whereNotNull()
            ->values();
    }
}
