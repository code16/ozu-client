<?php

namespace Code16\JockoClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class JockoClient
{
    public function getCollections(): array
    {
        return $this->http()->get('collections')->json();
    }

    public function getCollection(string $collectionKey): array
    {
        return $this->getCollections()[$collectionKey];
    }

    public function getConfig(): array
    {
        return $this->http()->get('config')->json();
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl(config('jocko-client.api_url'))
            ->withToken(config('jocko-client.api_token'))
            ->acceptJson();
    }
}
