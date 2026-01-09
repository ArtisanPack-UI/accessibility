<?php

namespace ArtisanPack\Accessibility\Tests\Unit;

use ArtisanPack\Accessibility\Laravel\A11yServiceProvider;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests for environment variable configuration overrides.
 *
 * ## Environment Variable Naming Convention
 *
 * This package uses the `ACCESSIBILITY_*` prefix for environment variables,
 * NOT `ARTISANPACK_ACCESSIBILITY_*`. This follows Laravel's convention where
 * the env() prefix is typically the package/feature name in uppercase.
 *
 * ### Mapping from Config Keys to Environment Variables
 *
 * Config keys are converted to environment variables by:
 * 1. Using `ACCESSIBILITY_` as the prefix (the package identifier)
 * 2. Converting the remaining path segments to uppercase
 * 3. Replacing dots with underscores
 *
 * Examples:
 * - `artisanpack.accessibility.wcag_thresholds.aa` → `ACCESSIBILITY_WCAG_THRESHOLDS_AA`
 * - `artisanpack.accessibility.cache.default` → `ACCESSIBILITY_CACHE_DEFAULT`
 *
 * ### Rationale
 *
 * The `artisanpack.accessibility` config namespace is used internally for
 * organization under Laravel's config system, but environment variables
 * use the shorter `ACCESSIBILITY_` prefix for:
 * - Brevity in .env files
 * - Consistency with other Laravel packages
 * - Avoiding excessively long variable names
 *
 * @see config/accessibility.php for where env() calls define the mappings
 */
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

        $config = include __DIR__ . '/../../config/accessibility.php';
        $app['config']->set('artisanpack.accessibility', $config);
    }

    protected function tearDown(): void
    {
        // Clean up to avoid leaking into other tests
        putenv('ACCESSIBILITY_WCAG_THRESHOLDS_AA');
        unset($_ENV['ACCESSIBILITY_WCAG_THRESHOLDS_AA'], $_SERVER['ACCESSIBILITY_WCAG_THRESHOLDS_AA']);
        // Reset the config to defaults for safety (new app instances will reload anyway)
        Config::set('artisanpack.accessibility.wcag_thresholds.aa', 4.5);

        parent::tearDown();
    }

    #[Test]
    public function environment_variables_can_override_configuration()
    {
        $this->assertEquals(6.0, Config::get('artisanpack.accessibility.wcag_thresholds.aa'));
    }
}
