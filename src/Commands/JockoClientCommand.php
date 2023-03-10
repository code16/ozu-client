<?php

namespace Code16\JockoClient\Commands;

use Illuminate\Console\Command;

class JockoClientCommand extends Command
{
    public $signature = 'jocko-client';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
