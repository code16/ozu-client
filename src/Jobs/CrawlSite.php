<?php

namespace Code16\JockoClient\Jobs;
use Code16\JockoClient\Support\Crawler\Observer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Export\Crawler\LocalClient;
use Spatie\Export\Destination;

class CrawlSite
{
    use Dispatchable;

    public function handle(UrlGenerator $urlGenerator, Destination $destination): void
    {
        $entry = $urlGenerator->to('/');

        (new Crawler(new LocalClient()))
            ->setCrawlObserver(new Observer($entry, $destination))
            ->setCrawlProfile(new CrawlInternalUrls($entry))
            ->startCrawling($entry);
    }
}
