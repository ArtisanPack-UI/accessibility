<?php

namespace Tests;

use ArtisanPack\Accessibility\Laravel\A11yServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load test migrations
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            \ArtisanPackUI\Ai\AiServiceProvider::class,
            A11yServiceProvider::class,
            \Laravel\Sanctum\SanctumServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
