<?php

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Laravel;

use ArtisanPack\Accessibility\Core\Config as BaseConfig;

class LaravelConfig extends BaseConfig
{
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }
}