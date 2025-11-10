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

use ArtisanPack\Accessibility\Console\AuditColorsCommand;
use ArtisanPack\Accessibility\Console\GeneratePaletteCommand;
use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\Performance\BatchProcessor;
use ArtisanPack\Accessibility\Core\Caching\CacheManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Core\Contracts\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use ArtisanPack\Accessibility\Events\ColorContrastChecked;
use ArtisanPack\Accessibility\Listeners\LogColorContrastCheck;

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
	 * The event listener mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		ColorContrastChecked::class => [
			LogColorContrastCheck::class,
		],
	];

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
		$this->app->singleton( Config::class, LaravelConfig::class );

		$this->app->singleton( 'a11y', function ( $app ) {
			$config         = $app->make( Config::class );
			$cacheConfig    = $config->get( 'accessibility.cache' );
			$cacheManager   = new CacheManager( $cacheConfig );
			$colorGenerator = new AccessibleColorGenerator( null, null, $cacheManager );
			$batchProcessor = new BatchProcessor( $colorGenerator, $colorGenerator->getCache() );

			return new A11y( $config, null, $colorGenerator, $batchProcessor );
		} );

		$this->mergeConfigFrom(
			__DIR__ . '/../../config/accessibility.php', 'accessibility'
		);

		$this->app->afterResolving( 'eloquent.factory', function ( $factory ) {
			$factory->load( __DIR__ . '/../../database/factories' );
		} );
	}

	/**
	 * Perform post-registration booting of services.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function boot(): void
	{
		RateLimiter::for( 'api', function ( Request $request ) {
			return Limit::perMinute( 60 )->by( $request->user()?->id ?: $request->ip() );
		} );

		$this->loadViewsFrom( __DIR__ . '/../../resources/views', 'accessibility' );

		$this->loadRoutesFrom( __DIR__ . '/../../routes/api.php' );

		if ( $this->app->runningInConsole() ) {
			$this->publishes(
				[
					__DIR__ . '/../../config/accessibility.php' => config_path( 'accessibility.php' ),
				], 'config'
			);

			// Register CLI commands
			$this->commands( [
								 AuditColorsCommand::class,
								 GeneratePaletteCommand::class,
							 ] );
		}

		$this->validateConfig( config( 'accessibility' ) );

		foreach ( $this->listen as $event => $listeners ) {
			foreach ( $listeners as $listener ) {
				$this->app['events']->listen( $event, $listener );
			}
		}
	}

	/**
	 * @param  $config
	 * @return void
	 */
	protected function validateConfig( $config ): void
	{
		$validator = Validator::make(
			$config, [
					   'wcag_thresholds.aa'                => 'required|numeric|min:1|max:21',
					   'wcag_thresholds.aaa'               => 'required|numeric|min:1|max:21',
					   'large_text_thresholds.font_size'   => 'required|integer|min:1',
					   'large_text_thresholds.font_weight' => 'required|string',
					   'cache.default'                     => 'required|string|in:array,file,null',
					   'cache.stores.array.limit'          => 'required_if:cache.default,array|integer|min:0',
					   'cache.stores.file.path'            => 'required_if:cache.default,file|string',
				   ]
		);

		if ( $validator->fails() ) {
			throw new InvalidArgumentException( 'Invalid accessibility configuration: ' . $validator->errors()->first() );
		}
	}
}
