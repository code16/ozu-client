<?php

namespace Code16\OzuClient;

use Code16\OzuClient\Support\Api\OzuApiEndpoints;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Storage;

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
        // Set general collection settings
        $this->http()->post(
            url: OzuApiEndpoints::GeneralCollectionSettings->endpoint($collectionKey),
            data: collect($collectionData)->only(['label', 'icon', 'hasPublicationState', 'isCreatable', 'isDeletable', 'order'])->toArray()
        );

        // Configure collection's list
        $this->http()->post(
            url: OzuApiEndpoints::CollectionListConfiguration->endpoint($collectionKey),
            data: collect($collectionData)->only(['list'])->toArray()
        );

        // Configure collection's form
        $this->http()->post(
            url: OzuApiEndpoints::CollectionFormConfiguration->endpoint($collectionKey),
            data: collect($collectionData)->only(['form'])->toArray()
        );

        // Configure collection's custom fields'
        $this->http()->post(
            url: OzuApiEndpoints::CollectionCustomFieldsConfiguration->endpoint($collectionKey),
            data: collect($collectionData)->only(['customFields'])->toArray()
        );

        // Configure collection's auto deploy fields
        $this->http()->post(
            url: OzuApiEndpoints::CollectionAutoDeployFields->endpoint($collectionKey),
            data: collect($collectionData)->only(['autoDeployDateField'])->toArray()
        );
    }

    public function seed(string $collection, array $payload): mixed
    {
        return $this->http()->post(
            sprintf('/collections/%s/seed', $collection),
            $payload
        )->json();
    }

    public function seedFile(string $collection, int $id, string $field, string $path): mixed
    {
        return $this->http()
            ->attach('file', file_get_contents($path), basename($path))
            ->post(
                sprintf('/collections/%s/seed/%s/file', $collection, $id),
                [
                    'field' => $field,
                ]
            )->getBody()?->getContents();
    }

    public function downloadOzuDatabase(): ?string
    {
        $data = Http::withToken($this->apiKey)
            ->baseUrl(
                sprintf(
                    '%s/api/websites/%s',
                    rtrim($this->apiHost, '/'),
                    $this->apiKey,
                )
            )
            ->acceptJson()
            ->throw()
            ->get('/database');

        if ($data->successful()) {
            Storage::put('tmp/ozu.sql', $data->body());

            return Storage::path('tmp/ozu.sql');
        }

        return null;
    }

    public function downloadOzuAssets(): ?string
    {
        $data = Http::withToken($this->apiKey)
            ->baseUrl(
                sprintf(
                    '%s/api/websites/%s',
                    rtrim($this->apiHost, '/'),
                    $this->apiKey,
                )
            )
            ->acceptJson()
            ->throw()
            ->get('/assets');

        if ($data->successful()) {
            Storage::put('tmp/ozu-assets.zip', $data->body());

            return Storage::path('tmp/ozu-assets.zip');
        }

        return null;
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
