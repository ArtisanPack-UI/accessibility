<?php

namespace Tests\Unit;

use ArtisanPack\Accessibility\Laravel\A11yServiceProvider;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ConfigurationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [A11yServiceProvider::class];
    }

    #[Test]
    public function default_configuration_is_loaded()
    {
        $this->assertEquals(4.5, Config::get('accessibility.wcag_thresholds.aa'));
        $this->assertEquals(7.0, Config::get('accessibility.wcag_thresholds.aaa'));
        $this->assertEquals(18, Config::get('accessibility.large_text_thresholds.font_size'));
        $this->assertEquals('bold', Config::get('accessibility.large_text_thresholds.font_weight'));
        $this->assertEquals(1000, Config::get('accessibility.cache.stores.array.limit'));
        $this->assertEquals('array', Config::get('accessibility.cache.default'));
    }

    #[Test]
    public function configuration_can_be_overridden()
    {
        Config::set('accessibility.wcag_thresholds.aa', 5.0);
        $this->assertEquals(5.0, Config::get('accessibility.wcag_thresholds.aa'));
    }

    #[Test]
    public function invalid_configuration_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        Config::set('accessibility.wcag_thresholds.aa', 99);
        $this->app->make(A11yServiceProvider::class, ['app' => $this->app])->boot();
    }
}
