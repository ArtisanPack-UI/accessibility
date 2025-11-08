<?php

namespace Tests\Performance;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\BatchProcessor;
use ArtisanPack\Accessibility\Core\Caching\CacheManager;
use ArtisanPack\Accessibility\Core\WcagValidator;

class BenchmarkTest
{
    private array $colors = [
        '#ff0000',
        '#00ff00',
        '#0000ff',
        '#ffff00',
        '#00ffff',
        '#ff00ff',
        '#ffffff',
        '#000000',
        '#f87171',
        '#3b82f6',
    ];

    private function getBatchProcessor(string $cacheDriver): BatchProcessor
    {
        $config = [
            'default' => $cacheDriver,
            'stores' => [
                'array' => ['driver' => 'array'],
                'file' => ['driver' => 'file', 'path' => __DIR__ . '/cache'],
                'null' => ['driver' => 'null'],
            ],
        ];
        $cacheManager = new CacheManager($config);
        $colorGenerator = new AccessibleColorGenerator(new WcagValidator(), null, $cacheManager);
        return new BatchProcessor($colorGenerator, $cacheManager->store());
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     * @ParamProviders({"provideCacheDrivers"})
     */
    public function benchBatchProcessor(array $params): void
    {
        $batchProcessor = $this->getBatchProcessor($params['driver']);
        $batchProcessor->generateAccessibleTextColors($this->colors);
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     * @ParamProviders({"provideCacheDrivers"})
     */
    public function benchBatchProcessorWithTint(array $params): void
    {
        $batchProcessor = $this->getBatchProcessor($params['driver']);
        $batchProcessor->generateAccessibleTextColors($this->colors, true);
    }

    public function provideCacheDrivers(): \Generator
    {
        yield 'array' => ['driver' => 'array'];
        yield 'file' => ['driver' => 'file'];
        yield 'null' => ['driver' => 'null'];
    }
}
