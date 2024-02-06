<?php

namespace Code16\JockoClient\JockoCms\List;

use Carbon\Carbon;

class JockoDateColumn extends JockoColumn
{
    protected string $format = 'LL';

    public function setIsoFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function type(): string
    {
        return 'date';
    }
}
