<?php

namespace Code16\OzuClient\OzuCms\Storage;

use Exception;
use Illuminate\Support\Collection;

class OzuCustomFTPStorage extends OzuAbstractCustomStorage
{
    protected $host = null;
    protected $username = null;
    protected $password = null;
    protected $port = 21;
    protected $root = '/';
    protected $passive = true;
    protected $ssl = true;
    protected $timeout = 5;

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function setRootPath(string $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function setPassive(bool $passive): self
    {
        $this->passive = $passive;

        return $this;
    }

    public function setSsl(bool $ssl): self
    {
        $this->ssl = $ssl;

        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        if($timeout > 30)
        {
            throw new Exception('Timeout must be less than 30 seconds');
        }

        $this->timeout = $timeout;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'driver' => 'ftp',
            'host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,
            'port' => $this->port,
            'root' => $this->root,
            'passive' => $this->passive,
            'ssl' => $this->ssl,
            'timeout' => $this->timeout,
        ];
    }

    public function meetRequirements(): bool
    {
        if(!$this->host || !$this->username || !$this->password)
        {
            return false;
        }

        return true;
    }

    public function whatsMissing(): Collection
    {
        return collect([
            !$this->host ? 'Host' : null,
            !$this->username ? 'Username' : null,
            !$this->password ? 'Password' : null,
        ])->filter();
    }
}
