<?php

namespace Code16\OzuClient\Support\Database;

use Code16\OzuClient\Client;
use Code16\OzuClient\Eloquent\IsOzuModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class OzuProductionSeeder extends Seeder
{
    protected Client $client;
    private ?int $createdId = null;
    private ?string $currentCollectionKey = null;

    public function __construct()
    {
        $this->client = app(Client::class);
    }

    protected function createInOzu(Model $item): static
    {
        if (!in_array(IsOzuModel::class, class_uses_recursive($item))) {
            throw new \InvalidArgumentException($item::class." doesn't have the IsOzuModel trait");
        }

        $this->currentCollectionKey = $collectionKey = $item?->ozuCollectionKey();

        if (!$collectionKey) {
            throw new \InvalidArgumentException('Unable to retrieve collection key.');
        }

        $this->createdId = $this->client->seed($collectionKey, $item->toArray())['id'] ?? null;

        return $this;
    }

    protected function withFile(string $field, string $path, ?int $forceId = null): static
    {
        if (!$forceId && !$this->createdId) {
            throw new \InvalidArgumentException('No item created yet. Try calling createInOzu() first.');
        }

        if (!File::exists($path)) {
            throw new \InvalidArgumentException("File not found at path: {$path}");
        }

        $this->client->seedFile($this->currentCollectionKey, $forceId ?? $this->createdId, $field, $path);

        return $this;
    }

    /**
     * @param  array<string>  $paths
     */
    protected function withFileList(string $field, array $paths, ?int $forceId = null): static
    {
        foreach ($paths as $path) {
            $this->withFile($field, $path, $forceId);
        }

        return $this;
    }

    protected function id(): ?int
    {
        return $this->createdId;
    }
}
