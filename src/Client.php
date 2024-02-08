<?php

namespace Code16\JockoClient;

use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Client
{
    public function __construct(
        protected string $apiHost,
        protected ?string $apiKey,
        protected string $websiteKey,
        protected bool $shouldCache,
        protected bool $isPreview,
    ) {
    }

    public function getCollection(string $collectionKey): array
    {
        return $this->withCache($collectionKey, fn () =>
            $this->http()
                ->get($this->url("/collections/$collectionKey"))
                ->json('data')
        );
    }

    public function getSettings(): array
    {
        return $this->withCache('settings', fn () =>
            $this->http()
                ->get($this->url('/settings'))
                ->json('data')
        );
    }

    public function searchUrl(string $collectionKey): string
    {
        return $this->url("/collections/$collectionKey/search");
    }

    public function withCache(string $key, Closure $callback)
    {
        if(! $this->shouldCache()) {
            Cache::forget("jocko:$key");
            return $callback();
        }

        return Cache::rememberForever("jocko:$key", $callback);
    }

    /**
     * env: JOCKO_SHOULD_CACHE
     */
    public function shouldCache(): bool
    {
        return $this->shouldCache || $this->isExporting();
    }

    public function isExporting(): bool
    {
        return request()->hasHeader('X-Laravel-Export');
    }

    public function isPreview(): bool
    {
        return $this->isPreview && !$this->isExporting();
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
