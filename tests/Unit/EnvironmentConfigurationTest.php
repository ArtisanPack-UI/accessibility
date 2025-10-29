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
        putenv('ACCESSIBILITY_WCAG_THRESHOLDS_AA=6.0');
        $config = require __DIR__ . '/../../config/accessibility.php';
        $app['config']->set('accessibility', $config);
    }

    /** @test */
    public function environment_variables_can_override_configuration()
    {
        $this->assertEquals(6.0, Config::get('accessibility.wcag_thresholds.aa'));
    }
}
