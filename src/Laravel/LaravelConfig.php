<?php

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Laravel;

use ArtisanPack\Accessibility\Core\Contracts\Config;

class LaravelConfig implements Config
{
    /**
     * @param  string     $key
     * @param  mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        config([$key => $value]);
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return config()->has($key);
    }
}