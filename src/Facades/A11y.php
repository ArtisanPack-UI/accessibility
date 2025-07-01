<?php
/**
 * Accessibility Facade
 *
 * Provides a facade for the A11y class to make it easily accessible
 * throughout a Laravel application.
 *
 * @since      1.0.0
 * @package    ArtisanPackUI\Accessibility\Facades
 */

namespace ArtisanPackUI\Accessibility\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the A11y class.
 *
 * This facade provides a static interface to the A11y class, allowing
 * for easy access to accessibility methods throughout the application.
 *
 * @since 1.0.0
 * @see   \ArtisanPackUI\Accessibility\A11y
 */
class A11y extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * Returns the service container binding key for the A11y service.
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the component in the container.
	 */
	protected static function getFacadeAccessor()
	{
		return 'a11y';
	}
}
