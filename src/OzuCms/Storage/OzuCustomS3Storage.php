<?php

namespace Code16\OzuClient\OzuCms\Storage;

use Illuminate\Support\Collection;

class OzuCustomS3Storage extends OzuAbstractCustomStorage
{
    protected $bucket = null;
    protected $region = null;
    protected $key = null;
    protected $secret = null;
    protected $endpoint = null;
    protected $use_path_style_endpoint = false;

    public function setBucketName(string $bucket): self
    {
        $this->bucket = $bucket;

        return $this;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function setUsePathStyleEndpoint(bool $use_path_style_endpoint): self
    {
        $this->use_path_style_endpoint = $use_path_style_endpoint;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'driver' => 's3',
            'bucket' => $this->bucket,
            'region' => $this->region,
            'key' => $this->key,
            'secret' => $this->secret,
            'endpoint' => $this->endpoint,
            'use_path_style_endpoint' => $this->use_path_style_endpoint,
        ];
    }

    public function meetRequirements(): bool
    {
        if(!$this->bucket || !$this->region || !$this->key || !$this->secret || !$this->endpoint)
        {
            return false;
        }

        return true;
    }

    public function whatsMissing(): Collection
    {
        return collect([
            !$this->bucket ? 'Host' : null,
            !$this->region ? 'Region' : null,
            !$this->key ? 'Key' : null,
            !$this->secret ? 'Secret' : null,
            !$this->endpoint ? 'Endpoint' : null,
        ])->filter();
    }
}
