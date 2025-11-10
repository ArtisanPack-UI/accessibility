<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ArtisanPack\Accessibility\Laravel\A11yServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load test migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            A11yServiceProvider::class,
            \Laravel\Sanctum\SanctumServiceProvider::class,
        ];
    }
}
