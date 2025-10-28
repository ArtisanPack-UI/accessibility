<?php

use ArtisanPackUI\Accessibility\A11y;
use ArtisanPackUI\Accessibility\AccessibleColorGenerator;
use ArtisanPackUI\Accessibility\Constants;
use Tests\TestCase;

class CachingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        A11y::clearCache();
        AccessibleColorGenerator::clearCache();
    }

    public function test_contrast_ratio_is_cached()
    {
        $color1 = '#ffffff';
        $color2 = '#000000';

        $this->assertEquals(0, A11y::getCacheHits());
        $this->assertEquals(0, A11y::getCacheMisses());

        (new A11y())->a11yCheckContrastColor($color1, $color2);

        $this->assertEquals(0, A11y::getCacheHits());
        $this->assertEquals(1, A11y::getCacheMisses());

        (new A11y())->a11yCheckContrastColor($color1, $color2);

        $this->assertEquals(1, A11y::getCacheHits());
        $this->assertEquals(1, A11y::getCacheMisses());
    }

    public function test_find_closest_shade_is_cached()
    {
        $color = '#3b82f6'; // blue-500

        $this->assertEquals(0, AccessibleColorGenerator::getCacheHits());
        $this->assertEquals(0, AccessibleColorGenerator::getCacheMisses());

        (new AccessibleColorGenerator())->generateAccessibleTextColor($color, true);

        $this->assertEquals(0, AccessibleColorGenerator::getCacheHits());
        $this->assertEquals(1, AccessibleColorGenerator::getCacheMisses());

        (new AccessibleColorGenerator())->generateAccessibleTextColor($color, true);

        $this->assertEquals(1, AccessibleColorGenerator::getCacheHits());
        $this->assertEquals(1, AccessibleColorGenerator::getCacheMisses());
    }

    public function test_cache_eviction()
    {
        $a11y = new A11y();

        for ($i = 0; $i < Constants::CACHE_SIZE_LIMIT + 1; $i++) {
            $color1 = '#' . substr(md5(rand()), 0, 6);
            $color2 = '#' . substr(md5(rand()), 0, 6);
            $a11y->a11yCheckContrastColor($color1, $color2);
        }

        $this->assertCount(Constants::CACHE_SIZE_LIMIT, $this->getInaccessibleA11yCache());
    }

    private function getInaccessibleA11yCache(): array
    {
        $reflection = new ReflectionClass(A11y::class);
        $property = $reflection->getProperty('contrastCache');
        return $property->getValue();
    }
}
