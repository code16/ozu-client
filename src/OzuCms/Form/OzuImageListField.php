<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuImageListField extends OzuFileListField
{
    private ?string $cropRatio = null;

    public function type(): string
    {
        return 'imageList';
    }

    public function setCropRatio(string $cropRatio): self
    {
        $this->cropRatio = $cropRatio;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'cropRatio' => $this->cropRatio,
        ]);
    }
}
