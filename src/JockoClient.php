<?php

namespace Code16\JockoClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class JockoClient
{
    public function getCollection(string $collectionKey): array
    {
        return $this->http()
            ->get($this->url("/collections/$collectionKey"))
            ->json('data');
    }

    public function getSettings(): array
    {
        return $this->http()
            ->get($this->url('/settings'))
            ->json('data');
    }

    public function searchUrl(string $collectionKey): string
    {
        return $this->url("/search/$collectionKey");
    }

    protected function url(string $endpoint = ''): string
    {
        $host = rtrim(config('jocko-client.api_host'), '/');
        $websiteKey = config('jocko-client.website_key');
        $endpoint = ltrim($endpoint, '/');

        return "$host/api/v2/$websiteKey/$endpoint";
    }

    protected function http(): PendingRequest
    {
        return Http::withToken(config('jocko-client.api_token'))
            ->acceptJson()
            ->throw();
    }
}
