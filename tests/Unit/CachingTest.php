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
        AccessibleColorGenerator::clearCache();
    }

    public function test_find_closest_shade_is_cached()
    {
        $generator = new AccessibleColorGenerator();

        $this->assertEquals(0, AccessibleColorGenerator::getCacheHits());
        $this->assertEquals(0, AccessibleColorGenerator::getCacheMisses());

        $generator->generateAccessibleTextColor('#3b82f6', true);

        $this->assertEquals(0, AccessibleColorGenerator::getCacheHits());
        $this->assertEquals(1, AccessibleColorGenerator::getCacheMisses());

        $generator->generateAccessibleTextColor('#3b82f6', true);

        $this->assertEquals(1, AccessibleColorGenerator::getCacheHits());
        $this->assertEquals(1, AccessibleColorGenerator::getCacheMisses());
    }
}
