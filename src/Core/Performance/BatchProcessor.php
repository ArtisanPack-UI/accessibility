<?php

namespace ArtisanPack\Accessibility\Core\Performance;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\Events\BatchProcessingCompleted;
use ArtisanPack\Accessibility\Core\Events\CacheHit;
use ArtisanPack\Accessibility\Core\Events\CacheMiss;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;

class BatchProcessor
{
    protected AccessibleColorGenerator $colorGenerator;
    protected CacheInterface $cache;
    protected ?EventDispatcherInterface $dispatcher;

    public function __construct(AccessibleColorGenerator $colorGenerator, CacheInterface $cache, ?EventDispatcherInterface $dispatcher = null)
    {
        $this->colorGenerator = $colorGenerator;
        $this->cache = $cache;
        $this->dispatcher = $dispatcher;
    }

    public function generateAccessibleTextColors(
        array $backgroundColors,
        bool $tint = false,
        string $level = 'AA',
        bool $isLargeText = false
    ): array {
        $startTime = microtime(true);
        $originalKeys = array_keys($backgroundColors);
        $hexColors = [];
        $keyMap = [];
        $cacheHits = 0;

        foreach ($backgroundColors as $key => $color) {
            $hex = $this->colorGenerator->getHexFromColorString($color);
            if ($hex) {
                $hexColors[$key] = $hex;
                $cacheKey = $this->colorGenerator->getCacheKey($hex, $tint, $level, $isLargeText);
                $keyMap[$cacheKey] = $key;
            }
        }

        $cacheKeys = array_keys($keyMap);
        $cachedResults = $this->cache->getMultiple($cacheKeys);

        $results = [];
        $missedKeys = [];

        foreach ($cachedResults as $cacheKey => $value) {
            $originalKey = $keyMap[$cacheKey];
            if ($value !== null) {
                $results[$originalKey] = $value;
                $cacheHits++;
                if ($this->dispatcher) {
                    $this->dispatcher->dispatch(new CacheHit($cacheKey));
                }
            } else {
                $missedKeys[] = $originalKey;
                if ($this->dispatcher) {
                    $this->dispatcher->dispatch(new CacheMiss($cacheKey));
                }
            }
        }

        $generatedColors = [];
        if (!empty($missedKeys)) {
            foreach ($missedKeys as $key) {
                $color = $backgroundColors[$key];
                $generatedColor = $this->colorGenerator->generateAccessibleTextColor(
                    $color,
                    $tint,
                    $level,
                    $isLargeText
                );
                $results[$key] = $generatedColor;

                $hex = $hexColors[$key];
                $cacheKey = $this->colorGenerator->getCacheKey($hex, $tint, $level, $isLargeText);
                $generatedColors[$cacheKey] = $generatedColor;
            }

            if (!empty($generatedColors)) {
                $this->cache->setMultiple($generatedColors);
            }
        }

        // Ensure original order
        $finalResults = [];
        foreach ($originalKeys as $key) {
            $finalResults[$key] = $results[$key] ?? '#000000'; // Fallback for invalid colors
        }

        if ($this->dispatcher) {
            $duration = microtime(true) - $startTime;
            $this->dispatcher->dispatch(new BatchProcessingCompleted(count($backgroundColors), $cacheHits, $duration));
        }

        return $finalResults;
    }
}
