<?php

namespace Code16\JockoClient\Deploy\Jobs;
use Code16\JockoClient\Deploy\Crawler\Observer;
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
            ->setCrawlProfile(new class($entry) extends CrawlInternalUrls {
                public function shouldCrawl(UriInterface $url): bool
                {
                    return parent::shouldCrawl($url)
                        || config('jocko-client.deploy_url') && $url->getHost() === parse_url(config('jocko-client.deploy_url'), PHP_URL_HOST);
                }
            })
            ->startCrawling($entry);
    }
}
