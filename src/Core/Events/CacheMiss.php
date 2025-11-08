<?php

namespace ArtisanPack\Accessibility\Core\Events;

class CacheMiss
{
    public string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }
}
