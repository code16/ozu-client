<?php

namespace Code16\OzuClient\OzuCms\List;

use Carbon\Carbon;

class OzuDateColumn extends OzuColumn
{
    public function type(): string
    {
        return 'date';
    }
}
