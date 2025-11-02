<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Orchestra\Testbench\TestCase as Orchestra;
use ArtisanPack\Accessibility\Laravel\A11yServiceProvider;

class TestCase extends Orchestra
{
	protected function getPackageProviders( $app )
	{
		return [
			A11yServiceProvider::class,
		];
	}
}
