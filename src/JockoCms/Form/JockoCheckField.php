<?php

namespace Code16\JockoClient\JockoCms\Form;

class JockoCheckField extends JockoField
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
