<?php

namespace Code16\JockoClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class JockoClient
{
    public function getCollection(string $collectionKey): array
    {
        return $this->http()->get("/collections/$collectionKey")->json('data');
    }

    public function getSettings(): array
    {
        return $this->http()->get('/settings')->json('data');
    }

    protected function http(): PendingRequest
    {
        $host = config('jocko-client.api_host');
        $token = config('jocko-client.api_token');
        $websiteKey = config('jocko-client.website_key');

        return Http::baseUrl(rtrim($host, '/') . '/api/v2/' . $websiteKey)
            ->withToken($token)
            ->acceptJson();
    }
}
