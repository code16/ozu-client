<?php

namespace Code16\OzuClient\Deploy\Crawler;

use Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Export\Crawler\Observer as BaseObserver;

class Observer extends BaseObserver
{
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        try {
            parent::crawled($url, $response, $foundOnUrl, $linkText);
        } catch (\RuntimeException $e) {
            if (preg_match('/returned status code \[4\d\d]/', $e->getMessage())) {
                Log::warning("Crawled URL {$url} found on {$foundOnUrl} returned status code 4xx", [
                    'url' => (string) $url,
                    'status_code' => $response->getStatusCode(),
                    'found_on_url' => (string) $foundOnUrl,
                    'link_text' => $linkText,
                ]);

                return;
            }
            throw $e;
        }
    }
}
