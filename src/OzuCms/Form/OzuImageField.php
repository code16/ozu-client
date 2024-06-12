<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuImageField extends OzuField
{
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private int $maxFileSizeInMB = 5;
    private bool $hasLegend = false;

    public function setHasLegend(bool $hasLegend = true): self
    {
        $this->hasLegend = $hasLegend;

        return $this;
    }

    public function setAllowedExtensions(array $extensions): self
    {
        $this->allowedExtensions = $extensions;

        return $this;
    }

    public function setMaxFileSizeInMB(int $maxFileSizeInMB): self
    {
        $this->maxFileSizeInMB = $maxFileSizeInMB;

        return $this;
    }

    public function type(): string
    {
        return 'image';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'hasLegend' => $this->hasLegend,
            'allowedExtensions' => $this->allowedExtensions,
            'maxFileSize' => $this->maxFileSizeInMB,
        ]);
    }
}
