<?php

namespace Code16\OzuClient;

use Code16\OzuClient\Exceptions\OzuClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Storage;

class Client
{
    public function __construct(
        protected string $apiHost,
        protected ?string $apiKey,
        protected string $apiVersion,
    ) {
    }

    public function updateCollectionSharpConfiguration(string $collectionKey, array $collectionData): void
    {
        $this->http()
            ->post(
                sprintf('/collections/%s/configure', $collectionKey),
                $collectionData
            );
    }

    public function deleteCollectionSharpConfigurationExcept(array $collections): void
    {
        $this->http()
            ->delete(
                '/collections/configure',
                [
                    'except' => $collections,
                ]
            );
    }

    public function updateSettingsSharpConfiguration(array $settings)
    {
        return $this->http()
            ->post(
                '/settings/configure',
                $settings
            );
    }

    public function seed(string $collection, array $payload): mixed
    {
        $res = $this->http()
            ->post(sprintf('/collections/%s/seed', $collection), $payload);

        if (!$res->successful()) {
            $error = $res->json('error') ?? $res->body();
            throw new OzuClientException("Error seeding {$collection}: {$error}");
        }

        return $res->json();
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
            )
            ->getBody()
            ?->getContents();
    }

    public function downloadOzuDatabase(): ?string
    {
        $data = $this->http()
            ->get('/websites/database');

        if ($data->successful()) {
            Storage::put('tmp/ozu.sql', $data->body());

            return Storage::path('tmp/ozu.sql');
        }

        return null;
    }

    public function downloadOzuAssets(): ?string
    {
        $data = $this->http()
            ->get('/websites/assets');

        if ($data->successful()) {
            Storage::put('tmp/ozu-assets.zip', $data->body());

            return Storage::path('tmp/ozu-assets.zip');
        }

        return null;
    }

    public function fetchSettings(): ?array
    {
        $data = $this->http()
            ->get('/settings/fetch');

        if ($data->successful()) {
            return $data->json();
        }

        return null;
    }

    public function apiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getWebsiteKey(): ?string
    {
        return $this->http()->get('/website')->json()['key'] ?? null;
    }

    protected function http(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->baseUrl(
                sprintf(
                    '%s/api/%s',
                    rtrim($this->apiHost, '/'),
                    $this->apiVersion,
                )
            )
            ->acceptJson()
            ->throw();
    }
}
