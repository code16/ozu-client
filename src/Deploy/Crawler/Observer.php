<?php

namespace Code16\OzuClient\Deploy\Crawler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Export\Crawler\Observer as BaseObserver;

class Observer extends BaseObserver
{
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        try {
            parent::crawled($url, $response, $foundOnUrl, $linkText);
        } catch(\RuntimeException $e) {
            if(!app()->hasDebugModeEnabled() && preg_match('/returned status code \[4\d\d]/', $e->getMessage())) {
                return;
            }
            throw $e;
        }
    }
}
