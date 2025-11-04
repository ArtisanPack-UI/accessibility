<?php

namespace Tests\Unit;

use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Core\Contracts\Config;
use ArtisanPack\Accessibility\Core\WcagValidator;
use PHPUnit\Framework\TestCase;

class FrameworkAgnosticTest extends TestCase
{
    public function test_a11y_can_be_instantiated_without_laravel()
    {
        $config = $this->createMock(Config::class);
        $config->method('get')->willReturn(null);
        $config->method('set');
        $config->method('has')->willReturn(false);
        $a11y = new A11y($config, null);

        $this->assertInstanceOf(A11y::class, $a11y);
    }

    public function test_a11y_get_contrast_color()
    {
        $config = $this->createMock(Config::class);
        $wcagValidator = new WcagValidator();
        $a11y = new A11y($config, $wcagValidator);

        $this->assertEquals('#000000', $a11y->a11yGetContrastColor('#FFFFFF'));
        $this->assertEquals('#FFFFFF', $a11y->a11yGetContrastColor('#000000'));
    }

    public function test_a11y_css_var_black_or_white()
    {
        $config = $this->createMock(Config::class);
        $wcagValidator = new WcagValidator();
        $a11y = new A11y($config, $wcagValidator);

        $this->assertEquals('black', $a11y->a11yCSSVarBlackOrWhite('#FFFFFF'));
        $this->assertEquals('white', $a11y->a11yCSSVarBlackOrWhite('#000000'));
    }

    public function test_a11y_check_contrast_color()
    {
        $config = $this->createMock(Config::class);
        $wcagValidator = new WcagValidator();
        $a11y = new A11y($config, $wcagValidator);

        $this->assertTrue($a11y->a11yCheckContrastColor('#FFFFFF', '#000000'));
        $this->assertFalse($a11y->a11yCheckContrastColor('#FFFFFF', '#FFFFFF'));
    }
}
