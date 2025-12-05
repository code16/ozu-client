<?php

namespace Code16\OzuClient\OzuCms\Form;

use Code16\OzuClient\Exceptions\OzuClientException;

class OzuEditorField extends OzuField
{
    private bool $withoutParagraphs = false;

    private bool $hideToolbar = false;

    private array $toolbar = [
        OzuEditorToolbarButton::Bold,
        OzuEditorToolbarButton::Italic,
        OzuEditorToolbarButton::Separator,
        OzuEditorToolbarButton::BulletList,
        OzuEditorToolbarButton::Link,
    ];

    private int $height = 200;

    private ?int $maxHeight = null;

    private int $maxFileSize = 5;

    private ?array $allowedExtensions = null;

    private ?string $cropRatio = null;

    public function setWithoutParagraphs(): self
    {
        $this->withoutParagraphs = true;

        return $this;
    }

    public function setToolbar(array $toolbar): self
    {
        $this->hideToolbar = false;
        $this->toolbar = $toolbar;

        return $this;
    }

    public function hideToolbar(): self
    {
        $this->hideToolbar = true;

        return $this;
    }

    public function setHeight(int $height, ?int $maxHeight = null): self
    {
        $this->height = $height;
        $this->maxHeight = $maxHeight;

        return $this;
    }

    public function setMaxFileSize(int $maxFileSize): self
    {
        if (!in_array(OzuEditorToolbarButton::Image, $this->toolbar) && !in_array(OzuEditorToolbarButton::File, $this->toolbar)) {
            throw new OzuClientException('You should allow Uploads by adding OzuEditorToolbarButton::Image or OzuEditorToolbarButton::File in toolbar configuration before setting max file size');
        }

        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    public function setCropRatio(string $cropRatio): self
    {
        if (!in_array(OzuEditorToolbarButton::Image, $this->toolbar)) {
            throw new OzuClientException('You should allow image uploads by adding OzuEditorToolbarButton::Image in toolbar configuration before setting image crop ratio');
        }

        $this->cropRatio = $cropRatio;

        return $this;
    }

    public function setAllowedExtensions(array $extensions): self
    {
        if (!in_array(OzuEditorToolbarButton::File, $this->toolbar)) {
            throw new OzuClientException('You should allow uploads by adding OzuEditorToolbarButton::File or OzuEditorToolbarButton::Image in toolbar configuration before setting the allowed extensions for uploads');
        }

        // formatting extensions to be compatible with the editor
        $this->allowedExtensions = collect($extensions)
            ->map(fn ($filter) => str($filter)->trim()->start('.')->value())
            ->all();

        return $this;
    }

    public function type(): string
    {
        return 'editor';
    }

    public function toArray(): array
    {
        if (in_array(OzuEditorToolbarButton::File, $this->toolbar) && empty($this->allowedExtensions)) {
            throw new OzuClientException('You have to set allowed extensions (via ->setAllowedExtensions()’s method) when using the OzuEditorToolbarButton::File in your toolbar');
        }

        return array_merge(parent::toArray(), [
            'withoutParagraphs' => $this->withoutParagraphs,
            'hideToolbar' => $this->hideToolbar,
            'toolbar' => collect($this->toolbar)->map(fn ($item) => $item->value)->toArray(),
            ...(!empty($this->allowedExtensions) ? ['allowedExtensions' => $this->allowedExtensions] : []),
            'height' => $this->height,
            'maxHeight' => $this->maxHeight,
            'maxFileSize' => $this->maxFileSize,
            'cropRatio' => $this->cropRatio,
        ]);
    }
}
