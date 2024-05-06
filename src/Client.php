<?php

namespace Code16\OzuClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Client
{
    public function __construct(
        protected string $apiHost,
        protected ?string $apiKey,
        protected string $apiVersion,
        protected string $websiteKey,
    ) {
    }

    public function updateCollectionSharpConfiguration(string $collectionKey, array $collectionData): void
    {
        $this->http()->post(
            sprintf('/collections/%s/configure', $collectionKey),
            $collectionData
        );
    }

    public function apiKey(): ?string
    {
        return $this->apiKey;
    }

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
