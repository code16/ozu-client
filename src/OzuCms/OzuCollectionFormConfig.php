<?php

namespace Code16\OzuClient\OzuCms;

use Closure;
use Code16\OzuClient\OzuCms\Form\OzuBelongsToField;
use Code16\OzuClient\OzuCms\Form\OzuEditorField;
use Code16\OzuClient\OzuCms\Form\OzuEditorToolbarEnum;
use Code16\OzuClient\OzuCms\Form\OzuField;
use Code16\OzuClient\OzuCms\Form\OzuImageField;
use Code16\OzuClient\OzuCms\Form\OzuTextField;
use Illuminate\Support\Collection;

class OzuCollectionFormConfig
{
    protected ?OzuTextField $titleField;

    protected bool $hideTitleField = false;

    protected ?OzuImageField $coverField;

    protected bool $hideCoverField = false;

    protected ?OzuEditorField $contentField;

    protected bool $hideContentField = false;

    protected array $fields = [];

    protected ?OzuBelongsToField $belongsToField = null;

    public function addCustomField(OzuField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function configureTitleField(Closure $callback): self
    {
        unset($this->titleField);

        $titleField = $this->titleField();
        $this->titleField = tap($titleField, fn (&$titleField) => $callback($titleField));

        return $this;
    }

    public function hideTitleField(bool $hideTitleField = true): self
    {
        $this->hideTitleField = $hideTitleField;

        return $this;
    }

    public function configureCoverField(Closure $callback): self
    {
        unset($this->coverField);

        $coverField = $this->coverField();
        $this->coverField = tap($coverField, fn (&$coverField) => $callback($coverField));

        return $this;
    }

    public function hideCoverField(bool $hideCoverField = true): self
    {
        $this->hideCoverField = $hideCoverField;

        return $this;
    }

    public function configureContentField(Closure $callback): self
    {
        unset($this->contentField);

        $contentField = $this->contentField();
        $this->contentField = tap($contentField, fn (&$contentField) => $callback($contentField));

        return $this;
    }

    public function hideContentField(bool $hideContentField = true): self
    {
        $this->hideContentField = $hideContentField;

        return $this;
    }

    public function declareBelongsToField(string $ozuModelClass, string $label, bool $required = true): self
    {
        $ozuCollectionKey = app($ozuModelClass)->ozuCollectionKey();

        $this->belongsToField = (new OzuBelongsToField($ozuCollectionKey))
            ->setLabel($label)
            ->setClearable(! $required)
            ->setValidationRules($required ? ['required'] : []);

        return $this;
    }

    public function customFields(): Collection
    {
        return collect(
            [
                $this->belongsToField,
                ...$this->fields,
            ])
            ->whereNotNull()
            ->values();
    }

    public function titleField(): ?OzuTextField
    {
        if ($this->hideTitleField) {
            return null;
        }

        if (! isset($this->titleField)) {
            $this->titleField = OzuField::makeText('title');
        }

        return $this->titleField;
    }

    public function coverField(): ?OzuImageField
    {
        if ($this->hideCoverField) {
            return null;
        }

        if (! isset($this->coverField)) {
            $this->coverField = OzuField::makeImage('cover')
                ->setMaxFileSizeInMB(3);
        }

        return $this->coverField;
    }

    public function contentField(): ?OzuEditorField
    {
        if ($this->hideContentField) {
            return null;
        }

        if (! isset($this->contentField)) {
            $this->contentField = OzuField::makeEditor('content')
                ->setToolbar([
                    OzuEditorToolbarEnum::Bold,
                    OzuEditorToolbarEnum::Italic,
                    OzuEditorToolbarEnum::Separator,
                    OzuEditorToolbarEnum::BulletList,
                    OzuEditorToolbarEnum::Link,
                ]);
        }

        return $this->contentField;
    }
}
