<?php

namespace Digitalshopfront\Accessibility;

use Illuminate\Support\ServiceProvider;

class A11yServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton( 'a11y', function ( $app ) {
            return new A11y();
        } );
    }
}
