<?php

namespace Code16\OzuClient\Deploy\Jobs;
use Code16\OzuClient\Deploy\Crawler\Observer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Bus\Dispatchable;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Export\Crawler\LocalClient;
use Spatie\Export\Destination;

class CrawlSite
{
    use Dispatchable;

    public function handle(UrlGenerator $urlGenerator, Destination $destination): void
    {
        $entry = config('app.url');

        (new Crawler(new LocalClient()))
            ->setCrawlObserver(new Observer($entry, $destination))
            ->setCrawlProfile(new CrawlInternalUrls($entry))
            ->startCrawling($entry);
    }
}
