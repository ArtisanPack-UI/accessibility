<?php

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Core\Contracts;

interface Config
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function has(string $key): bool;
}
