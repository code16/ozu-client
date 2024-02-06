<?php

namespace Code16\JockoClient\JockoCms\Form;

class JockoEditorField extends JockoField
{
    private bool $withoutParagraphs = false;
    private bool $hideToolbar = false;
    private array $toolbar = [
        'B', 'I', '|', 'UL', '|', 'A'
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
}
