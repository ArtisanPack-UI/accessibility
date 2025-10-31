<?php

namespace ArtisanPackUI\Accessibility\Tests\Unit;

use ArtisanPackUI\Accessibility\WcagValidator;
use PHPUnit\Framework\TestCase;

class WcagValidatorTest extends TestCase
{
    private WcagValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new WcagValidator();
    }

    /** @dataProvider contrastRatioDataProvider */
    public function test_calculates_contrast_ratio_correctly($color1, $color2, $expectedRatio)
    {
        $this->assertEqualsWithDelta($expectedRatio, $this->validator->calculateContrastRatio($color1, $color2), 0.01);
    }

    public static function contrastRatioDataProvider(): array
    {
        return [
            ['#FFFFFF', '#000000', 21],
            ['#767676', '#FFFFFF', 4.54],
            ['#FF0000', '#FFFFFF', 3.99],
        ];
    }

    /** @dataProvider wcagComplianceDataProvider */
    public function test_checks_wcag_compliance_correctly($color1, $color2, $level, $isLargeText, $expected)
    {
        $this->assertSame($expected, $this->validator->checkContrast($color1, $color2, $level, $isLargeText));
    }

    public static function wcagComplianceDataProvider(): array
    {
        return [
            // AA Normal Text
            ['#767676', '#FFFFFF', 'AA', false, true],
            ['#8A8A8A', '#FFFFFF', 'AA', false, false],

            // AA Large Text
            ['#8A8A8A', '#FFFFFF', 'AA', true, true],
            ['#979797', '#FFFFFF', 'AA', true, false],

            // AAA Normal Text
            ['#595959', '#FFFFFF', 'AAA', false, true],
            ['#696969', '#FFFFFF', 'AAA', false, false],

            // AAA Large Text
            ['#767676', '#FFFFFF', 'AAA', true, true],
            ['#8A8A8A', '#FFFFFF', 'AAA', true, false],

            // Non-text
            ['#949494', '#FFFFFF', 'NON-TEXT', false, true],
            ['#959595', '#FFFFFF', 'NON-TEXT', false, false],
        ];
    }
}
