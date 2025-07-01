<?php
/**
 * Accessibility Service Provider
 *
 * Registers the accessibility services with the Laravel application.
 *
 * @since      1.0.0
 * @package    ArtisanPackUI\Accessibility
 */

namespace ArtisanPackUI\Accessibility;

use Illuminate\Support\ServiceProvider;

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
		$this->app->singleton( 'a11y', function ( $app ) {
			return new A11y();
		} );
	}
}
