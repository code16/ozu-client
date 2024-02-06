<?php

namespace Code16\JockoClient\JockoCms\Form;

class JockoDateField extends JockoField
{
    protected bool $hasTime = false;

    public function setHasTime(bool $hasTime = true): self
    {
        $this->hasTime = $hasTime;

        return $this;
    }

    public function type(): string
    {
        return 'date';
    }
}
