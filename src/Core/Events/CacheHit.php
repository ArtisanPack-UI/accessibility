<?php

namespace ArtisanPack\Accessibility\Core\Events;

class CacheHit
{
    public string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }
}
