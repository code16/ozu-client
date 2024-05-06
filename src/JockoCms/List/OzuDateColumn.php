<?php

namespace Code16\OzuClient\OzuCms\List;

use Carbon\Carbon;

class OzuDateColumn extends OzuColumn
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
