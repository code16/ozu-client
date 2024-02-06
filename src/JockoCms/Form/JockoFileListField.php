<?php

namespace Code16\JockoClient\JockoCms\Form;

class JockoFileListField extends JockoField
{
    protected int $maxItems = 10;
    protected bool $hasLegend = false;

    public function setMaxItems(int $maxItems): self
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    public function setHasLegend(bool $hasLegend = true): self
    {
        $this->hasLegend = $hasLegend;

        return $this;
    }

    public function hasLegend(): bool
    {
        return $this->hasLegend;
    }

    private function buildUploadField(): SharpFormUploadField
    {
        return SharpFormUploadField::make('file')
            ->setMaxFileSize(5)
            ->setStorageDisk('local')
            ->setStorageBasePath(sprintf('data/%s/Posts/{id}', CurrentSharpWebsite::getKey()));
    }

    public function type(): string
    {
        return 'fileList';
    }
}
