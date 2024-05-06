<?php

namespace Code16\OzuClient\OzuCms\Form;

class OzuCheckField extends OzuField
{
    public function __construct(
        protected string $key,
        protected string $text
    ) {
        parent::__construct($key);
    }

    public function type(): string
    {
        return 'check';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'text' => $this->text,
        ]);
    }
}
