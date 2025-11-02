<?php

namespace Tests\Unit;

use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Core\Config;
use ArtisanPack\Accessibility\Core\WcagValidator;

class FrameworkAgnosticTest extends TestCase
{
    public function test_a11y_can_be_instantiated_without_laravel()
    {
        $config = $this->createMock(Config::class);
        $wcagValidator = new WcagValidator();
        $a11y = new A11y($config, $wcagValidator);

        $this->assertInstanceOf(A11y::class, $a11y);
    }
}
