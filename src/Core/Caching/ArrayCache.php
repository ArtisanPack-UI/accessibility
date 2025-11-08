<?php

namespace ArtisanPack\Accessibility\Core\Caching;

use Psr\SimpleCache\CacheInterface;

class ArrayCache implements CacheInterface
{
    protected array $storage = [];
    protected int $limit;
    protected array $keys = [];

    public function __construct(int $limit = 1000)
    {
        $this->limit = $limit;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->storage[$key] ?? $default;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        if (count($this->storage) >= $this->limit) {
            $oldestKey = array_shift($this->keys);
            unset($this->storage[$oldestKey]);
        }

        $this->storage[$key] = $value;
        $this->keys[] = $key;

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->storage[$key]);
        $this->keys = array_diff($this->keys, [$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->storage = [];
        $this->keys = [];
        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }
}
