<?php

namespace ArtisanPack\Accessibility\Core\Caching;

use Psr\SimpleCache\CacheInterface;

class CacheManager
{
    protected array $config;
    protected array $stores = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function store(?string $name = null): CacheInterface
    {
        $name = $name ?? $this->getDefaultDriver();

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->resolve($name);
        }

        return $this->stores[$name];
    }

    protected function resolve(string $name): CacheInterface
    {
        $config = $this->getDriverConfig($name);
        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';

        if (!method_exists($this, $driverMethod)) {
            throw new \InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }

        return $this->{$driverMethod}($config);
    }

    protected function createArrayDriver(array $config): CacheInterface
    {
        return new ArrayCache($config['limit'] ?? 1000);
    }

    protected function createFileDriver(array $config): CacheInterface
    {
        if (!isset($config['path'])) {
            throw new \InvalidArgumentException("File cache requires a 'path' configuration.");
        }
        return new FileCache($config['path']);
    }


    protected function createNullDriver(): CacheInterface
    {
        return new NullCache();
    }

    protected function getDriverConfig(string $name): ?array
    {
        return $this->config['stores'][$name] ?? null;
    }

    public function getDefaultDriver(): string
    {
        return $this->config['default'];
    }
}
