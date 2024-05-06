<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuFileListField extends OzuField
{
    protected int $maxItems = 10;
    protected bool $hasLegend = false;
    private int $maxFileSizeInMB = 5;

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

    public function setMaxFileSize(int $maxFileSizeInMB): self
    {
        $this->maxFileSizeInMB = $maxFileSizeInMB;

        return $this;
    }

    public function type(): string
    {
        return 'fileList';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'maxItems' => $this->maxItems,
            'hasLegend' => $this->hasLegend,
            'maxFileSize' => $this->maxFileSizeInMB,
        ]);
    }
}
