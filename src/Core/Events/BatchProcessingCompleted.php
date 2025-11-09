<?php

namespace ArtisanPack\Accessibility\Core\Events;

class BatchProcessingCompleted
{
    public int $totalColors;
    public int $cacheHits;
    public float $duration;

    public function __construct(int $totalColors, int $cacheHits, float $duration)
    {
        $this->totalColors = $totalColors;
        $this->cacheHits = $cacheHits;
        $this->duration = $duration;
    }
}
