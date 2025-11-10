<?php

namespace ArtisanPack\Accessibility\Core\Caching;

use Psr\SimpleCache\CacheInterface;

class NullCache implements CacheInterface
{
    public function get(string $key, mixed $default = null): mixed { return $default; }
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool { return true; }
    public function delete(string $key): bool { return true; }
    public function clear(): bool { return true; }
    public function getMultiple(iterable $keys, mixed $default = null): iterable { return array_fill_keys(iterator_to_array($keys), $default); }
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool { return true; }
    public function deleteMultiple(iterable $keys): bool { return true; }
    public function has(string $key): bool { return false; }
}
