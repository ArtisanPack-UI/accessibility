<?php

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Core\Contracts;

interface Config
{
    /**
     * @param  string     $key
     * @param  mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool;
}
