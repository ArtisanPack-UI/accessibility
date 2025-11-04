<?php
/**
 * Accessibility Service Provider
 *
 * Registers the accessibility services with the Laravel application.
 *
 * @since   1.0.0
 * @package ArtisanPack\Accessibility
 */

namespace ArtisanPack\Accessibility\Laravel;

use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Core\Contracts\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Service provider for the Accessibility package.
 *
 * This class registers the A11y service as a singleton in the Laravel
 * service container, making it available throughout the application.
 *
 * @since 1.0.0
 */
class A11yServiceProvider extends ServiceProvider
{
    /**
     * Register the accessibility services.
     *
     * Binds the A11y class to the service container as a singleton
     * with the key 'a11y'.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Config::class, LaravelConfig::class);

        $this->app->singleton(
            'a11y', function ($app) {
                return $app->make(A11y::class);
            }
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/accessibility.php', 'accessibility'
        );
    }

    /**
     * Perform post-registration booting of services.
     *
     * @since  1.0.0
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                __DIR__ . '/../../config/accessibility.php' => config_path('accessibility.php'),
                ], 'config'
            );
        }

        $this->validateConfig(config('accessibility'));
    }

    /**
     * @param  $config
     * @return void
     */
    protected function validateConfig($config): void
    {
        $validator = Validator::make(
            $config, [
            'wcag_thresholds.aa' => 'required|numeric|min:1|max:21',
            'wcag_thresholds.aaa' => 'required|numeric|min:1|max:21',
            'large_text_thresholds.font_size' => 'required|integer|min:1',
            'large_text_thresholds.font_weight' => 'required|string',
            'cache_size' => 'required|integer|min:0',
            ]
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid accessibility configuration: ' . $validator->errors()->first());
        }
    }
}
