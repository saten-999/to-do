<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Render (and similar hosts) terminate TLS at the proxy, so force HTTPS URL generation in production.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
