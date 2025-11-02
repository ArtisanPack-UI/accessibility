<?php

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Core;

use ArtisanPack\Accessibility\Core\Contracts\Config as ConfigContract;

class Config implements ConfigContract
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        data_set($this->config, $key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return ! is_null($this->get($key));
    }
}