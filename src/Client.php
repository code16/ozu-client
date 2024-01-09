<?php

namespace Code16\JockoClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Client
{
    public function __construct(
        protected string $apiHost,
        protected ?string $apiKey,
        protected string $websiteKey,
        protected bool $shouldCache,
        protected bool $isExporting,
        protected bool $isPreview,
    ) {
    }

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
        return $this->url("/collections/$collectionKey/search");
    }

    /**
     * env: JOCKO_SHOULD_CACHE
     */
    public function shouldCache(): bool
    {
        return $this->shouldCache || $this->isExporting;
    }

    public function isExporting(): bool
    {
        return $this->isExporting;
    }

    public function isPreview(): bool
    {
        return $this->isPreview && !$this->isExporting;
    }

    public function apiKey(): ?string
    {
        return $this->apiKey;
    }

    protected function url(string $endpoint = ''): string
    {
        $host = rtrim($this->apiHost, '/');
        $websiteKey = $this->websiteKey;
        $endpoint = ltrim($endpoint, '/');

        return "$host/api/v2/$websiteKey/$endpoint";
    }

    protected function http(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->acceptJson()
            ->throw();
    }
}
