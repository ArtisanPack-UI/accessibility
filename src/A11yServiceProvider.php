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

		$this->mergeConfigFrom(
			__DIR__ . '/../config/accessibility.php', 'artisanpack-accessibility-temp'
		);

	}

	/**
	 * Perform post-registration booting of services.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function boot(): void
	{
		// 1. Merge the configuration correctly.
		$this->mergeConfiguration();

		// 2. Tag the config file for the scaffold command.
		if ( $this->app->runningInConsole() ) {
			$this->publishes( [
								  __DIR__ . '/../config/accessibility.php' => config_path( 'artisanpack/accessibility.php' ),
							  ], 'artisanpack-package-config' );
		}
	}


	/**
	 * Merges the package's default configuration with the user's customizations.
	 *
	 * This method ensures that the user's settings in `config/artisanpack.php`
	 * take precedence over the package's default values.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function mergeConfiguration(): void
	{
		// Get the package's default configuration.
		$packageDefaults = config( 'artisanpack-accessibility-temp', [] );

		// Get the user's custom configuration from config/artisanpack.php.
		$userConfig = config( 'artisanpack.accessibility', [] );

		// Merge them, with the user's config overwriting the defaults.
		$mergedConfig = array_replace_recursive( $packageDefaults, $userConfig );

		// Set the final, correctly merged configuration.
		config( [ 'artisanpack.accessibility' => $mergedConfig ] );
	}

}
