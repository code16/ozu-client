<?php

namespace Code16\JockoClient\Listeners;

use Code16\JockoClient\Eloquent\Concerns\ManagesSushiCache;
use Illuminate\Console\Events\CommandStarting;

class ClearSushiCache
{
    use ManagesSushiCache;

    public function handle(CommandStarting $event): void
    {
        if($event->command === 'export' || $event->command === 'cache:clear') {
            $this->clearSushiCache();
        }
    }
}
