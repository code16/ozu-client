<?php

namespace Code16\JockoClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Client
{
    public function __construct(
        protected string $apiHost,
        protected ?string $apiKey,
        protected string $apiVersion,
        protected string $websiteKey,
        protected bool $shouldCache,
        protected bool $isPreview,
    ) {
    }

//    public function getSettings(): array
//    {
//        return $this->http()
//            ->get($this->url('/settings'))
//            ->json('data');
//    }

    public function updateCollectionSharpConfiguration(string $collectionKey, array $collectionData): void
    {
        $this->http()->post(
            sprintf('/collections/%s/configure', $collectionKey),
            $collectionData
        );
    }

//    public function searchUrl(string $collectionKey): string
//    {
//        return $this->url("/collections/$collectionKey/search");
//    }
//
//    /**
//     * env: JOCKO_SHOULD_CACHE
//     */
//    public function shouldCache(): bool
//    {
//        return $this->shouldCache || $this->isExporting();
//    }
//
//    public function isExporting(): bool
//    {
//        return request()->hasHeader('X-Laravel-Export');
//    }
//
//    public function isPreview(): bool
//    {
//        return $this->isPreview && !$this->isExporting();
//    }

    public function apiKey(): ?string
    {
        return $this->apiKey;
    }
//
//    protected function url(string $endpoint = ''): string
//    {
//        return sprintf(
//            '%s/api/%s/%s/%s',
//            rtrim($this->apiHost, '/'),
//            $this->apiVersion,
//            $this->websiteKey,
//            ltrim($endpoint, '/'),
//        );
//    }

    protected function http(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->baseUrl(
                sprintf(
                    '%s/api/%s/%s',
                    rtrim($this->apiHost, '/'),
                    $this->apiVersion,
                    $this->websiteKey,
                )
            )
            ->acceptJson()
            ->throw();
    }
}
