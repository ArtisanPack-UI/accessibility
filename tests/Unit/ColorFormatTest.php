<?php

namespace Tests\Unit;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use Tests\TestCase;

class ColorFormatTest extends TestCase
{
    protected AccessibleColorGenerator $colorGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->colorGenerator = new AccessibleColorGenerator();
    }

    /**
     * @test
     * @dataProvider rgbProvider
     */
    public function it_converts_rgb_to_hex($rgb, $expectedHex)
    {
        $method = new \ReflectionMethod(AccessibleColorGenerator::class, 'rgbToHex');
        $method->setAccessible(true);
        $this->assertEquals($expectedHex, $method->invoke($this->colorGenerator, ...$rgb));
    }

    /**
     * @test
     * @dataProvider hslProvider
     */
    public function it_converts_hsl_to_hex($hsl, $expectedHex)
    {
        $method = new \ReflectionMethod(AccessibleColorGenerator::class, 'hslToHex');
        $method->setAccessible(true);
        $this->assertEquals($expectedHex, $method->invoke($this->colorGenerator, ...$hsl));
    }

    /**
     * @test
     * @dataProvider colorStringProvider
     */
    public function it_gets_hex_from_color_string($colorString, $expectedHex)
    {
        $method = new \ReflectionMethod(AccessibleColorGenerator::class, 'getHexFromColorString');
        $method->setAccessible(true);
        $this->assertEquals($expectedHex, $method->invoke($this->colorGenerator, $colorString));
    }

    public static function rgbProvider()
    {
        return [
            [[255, 255, 255], '#ffffff'],
            [[0, 0, 0], '#000000'],
            [[255, 0, 0], '#ff0000'],
            [[0, 255, 0], '#00ff00'],
            [[0, 0, 255], '#0000ff'],
        ];
    }

    public static function hslProvider()
    {
        return [
            [[0, 0, 100], '#ffffff'],
            [[0, 0, 0], '#000000'],
            [[0, 100, 50], '#ff0000'],
            [[120, 100, 50], '#00ff00'],
            [[240, 100, 50], '#0000ff'],
        ];
    }

    public static function colorStringProvider()
    {
        return [
            ['rgb(255, 255, 255)', '#ffffff'],
            ['rgb(0, 0, 0)', '#000000'],
            ['hsl(0, 100%, 50%)', '#ff0000'],
            ['hsl(120, 100%, 50%)', '#00ff00'],
            ['#ff0000', '#ff0000'],
            ['red-500', '#ef4444'],
        ];
    }
}
