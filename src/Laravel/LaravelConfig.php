<?php

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Laravel;

use ArtisanPack\Accessibility\Core\Contracts\Config;

class LaravelConfig implements Config
{
    public function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        config([$key => $value]);
    }

    public function has(string $key): bool
    {
        return config()->has($key);
    }
}
