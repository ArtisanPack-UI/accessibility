<?php

namespace Tests\Unit;

use ArtisanPack\Accessibility\PaletteGeneration\ColorHarmony;
use PHPUnit\Framework\TestCase;

class ColorHarmonyTest extends TestCase
{
    public function test_complementary()
    {
        $this->assertEquals('#ff0000', ColorHarmony::complementary('#00ffff'));
    }

    public function test_triadic()
    {
        $this->assertEquals([
            '#00ff00',
            '#0000ff',
        ], ColorHarmony::triadic('#ff0000'));
    }

    public function test_analogous()
    {
        $this->assertEquals([
            '#ff8000',
            '#ff0080',
        ], ColorHarmony::analogous('#ff0000'));
    }

    public function test_split_complementary()
    {
        $this->assertEquals([
            '#00ff80',
            '#0080ff',
        ], ColorHarmony::splitComplementary('#ff0000'));
    }
}