<?php

namespace Tests\Unit;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\Caching\CacheManager;
use ArtisanPack\Accessibility\Core\WcagValidator;
use Tests\TestCase;

class CachingTest extends TestCase
{
    public function test_find_closest_shade_is_cached()
    {
        $config = [
            'default' => 'array',
            'stores' => [
                'array' => ['driver' => 'array', 'limit' => 1000],
            ],
        ];
        $cacheManager = new CacheManager($config);
        $colorGenerator = new AccessibleColorGenerator(new WcagValidator(), null, $cacheManager);

        $color = '#ff0000';
        $level = 'AA';
        $isLargeText = false;
        $cacheKey = $colorGenerator->getCacheKey($color, true, $level, $isLargeText);

        $this->assertFalse($colorGenerator->getCache()->has($cacheKey));

        $colorGenerator->generateAccessibleTextColor($color, true, $level, $isLargeText);

        $this->assertTrue($colorGenerator->getCache()->has($cacheKey));
    }
}
