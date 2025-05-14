<?php

namespace ArtisanPackUI\Accessibility\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ArtisanPackUI\Accessibility\A11y
 */
class A11y extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'a11y';
	}
}
