<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuEditorField extends OzuField
{
    private bool $withoutParagraphs = false;
    private bool $hideToolbar = false;
    private array $toolbar = [
        OzuEditorToolbarEnum::Bold,
        OzuEditorToolbarEnum::Italic,
        OzuEditorToolbarEnum::Separator,
        OzuEditorToolbarEnum::BulletList,
        OzuEditorToolbarEnum::Link,
    ];
    private int $height = 200;
    private ?int $maxHeight = null;

    public function setWithoutParagraphs(): self
    {
        $this->withoutParagraphs = true;

        return $this;
    }

    public function setToolbar(array $toolbar): self
    {
        $this->toolbar = $toolbar;

        return $this;
    }

    public function hideToolbar(): self
    {
        $this->hideToolbar = true;

        return $this;
    }

    public function setHeight(int $height, int|null $maxHeight = null): self
    {
        $this->height = $height;
        $this->maxHeight = $maxHeight;

        return $this;
    }

    public function type(): string
    {
        return 'editor';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'withoutParagraphs' => $this->withoutParagraphs,
            'hideToolbar' => $this->hideToolbar,
            'toolbar' => collect($this->toolbar)->map(fn($item) => $item->value)->toArray(),
            'height' => $this->height,
            'maxHeight' => $this->maxHeight,
        ]);
    }
}
