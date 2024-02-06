<?php

namespace Code16\JockoClient\JockoCms\Form;

class JockoImageField extends JockoField
{
    private string|array|null $fileFilter = null;
    private int $maxFileSize = 5;
    private bool $hasLegend = false;

    public function setHasLegend(bool $hasLegend = true): self
    {
        $this->hasLegend = $hasLegend;

        return $this;
    }

    public function setFileFiler(string|array|null $fileFilter): self
    {
        $this->fileFilter = $fileFilter;

        return $this;
    }

    public function setMaxFileSize(int $maxFileSize): self
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    public function hasLegend(): bool
    {
        return $this->hasLegend;
    }

    public function type(): string
    {
        return 'image';
    }
}
