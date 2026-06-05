<?php

namespace Code16\OzuClient;

use Code16\OzuClient\Exceptions\OzuClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Log;
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

    public function getDeploymentTargets(): ?array
    {
        $res = $this->http()->get('/targets');

        if ($res->failed()) {
            throw new OzuClientException('Error fetching deployment targets: '.$res->body());
        }

        return $res->json();
    }

    public function getUndeployedPostsForTarget(int $targetId): ?array
    {
        $res = $this->http()->get("/targets/{$targetId}/undeployed-posts");

        if ($res->failed()) {
            throw new OzuClientException('Error fetching undeployed posts: '.$res->body());
        }

        return $res->json();
    }

    public function triggerDeployment(int $targetId): ?array
    {
        $deploy = $this->http()->post("/targets/{$targetId}/deploy");

        if ($deploy->failed()) {
            if ($deploy->status() === 404) {
                throw new OzuClientException('Deployment target not found.');
            }
            if ($deploy->status() === 400) {
                throw new OzuClientException('Deployment target is not ready.');
            }

            throw new OzuClientException('Error triggering deployment: '.$deploy->body());
        }

        return $deploy->json();
    }

    public function fetchDeploymentLogs(string $deploymentUuid, ?int $offset = 0): ?array
    {
        $getStreamUrl = $this->http()->get("/targets/{$deploymentUuid}/logs/stream");

        if ($getStreamUrl->failed()) {
            if (!empty($getStreamUrl->json()['message'])) {
                throw new OzuClientException($getStreamUrl->json()['message']);
            }
            throw new OzuClientException('Error fetching deployment logs stream URL: '.$getStreamUrl->body());
        }

        if ($getStreamUrl->json()['url'] ?? null) {
            $streamUrl = $getStreamUrl->json()['url'];
        } else {
            return null;
        }

        try {
            $response = Http::throw()->post($streamUrl, [
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                return null;
            }

            return $response->json();

        } catch (\Throwable $e) {
            throw new OzuClientException('Error fetching deployment logs: '.$response->body());
        }
    }

    public function streamDeploymentLogs(string $deploymentUuid, ?callable $onLog = null): ?string
    {
        $getStreamUrl = $this->http()->get("/targets/{$deploymentUuid}/logs/stream");

        if ($getStreamUrl->failed()) {
            if (!empty($getStreamUrl->json()['message'])) {
                throw new OzuClientException($getStreamUrl->json()['message']);
            }
            throw new OzuClientException('Error fetching deployment logs stream URL: '.$getStreamUrl->body());
        }

        if ($getStreamUrl->json()['url'] ?? null) {
            $streamUrl = $getStreamUrl->json()['url'];
        } else {
            return null;
        }

        Log::info("Streaming deployment logs from {$streamUrl}");

        $response = Http::withOptions([
            'stream' => true,
        ])->get($streamUrl);

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $chunk = $body->read(65536);
            $buffer .= $chunk;

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                if (trim($line) === '' || !str_starts_with($line, 'data: ')) {
                    continue;
                }

                $data = substr($line, 6); // Remove 'data: '

                if ($onLog) {
                    if ($onLog($data) === false) {
                        return null;
                    }
                } else {
                    echo $data;
                }
            }
        }

        if ($buffer !== '') {
            $line = $buffer;
            if (str_starts_with($line, 'data: ')) {
                $data = substr($line, 6);
                if ($onLog) {
                    $onLog($data);
                } else {
                    echo $data;
                }
            }
        }

        return null;
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
            ->acceptJson();
    }
}
