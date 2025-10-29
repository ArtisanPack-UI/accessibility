<?php

namespace ArtisanPackUI\Accessibility\Tests\Unit;

use ArtisanPackUI\Accessibility\A11yServiceProvider;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;

class EnvironmentConfigurationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [A11yServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set env var to override the default threshold for this test case only
        putenv('ACCESSIBILITY_WCAG_THRESHOLDS_AA=6.0');
        $_ENV['ACCESSIBILITY_WCAG_THRESHOLDS_AA'] = '6.0';
        $_SERVER['ACCESSIBILITY_WCAG_THRESHOLDS_AA'] = '6.0';

        $config = require __DIR__ . '/../../config/accessibility.php';
        $app['config']->set('accessibility', $config);
    }

    protected function tearDown(): void
    {
        // Clean up to avoid leaking into other tests
        putenv('ACCESSIBILITY_WCAG_THRESHOLDS_AA');
        unset($_ENV['ACCESSIBILITY_WCAG_THRESHOLDS_AA'], $_SERVER['ACCESSIBILITY_WCAG_THRESHOLDS_AA']);
        // Reset the config to defaults for safety (new app instances will reload anyway)
        Config::set('accessibility.wcag_thresholds.aa', 4.5);

        parent::tearDown();
    }

    /** @test */
    public function environment_variables_can_override_configuration()
    {
        $this->assertEquals(6.0, Config::get('accessibility.wcag_thresholds.aa'));
    }
}
