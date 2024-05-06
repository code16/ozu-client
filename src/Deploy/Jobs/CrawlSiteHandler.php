<?php

namespace Code16\OzuClient\Deploy\Jobs;

use Illuminate\Contracts\Bus\Dispatcher;

class CrawlSiteHandler
{
    public function __invoke(): void
    {
        app(Dispatcher::class)->dispatchNow(new CrawlSite());
    }
}
