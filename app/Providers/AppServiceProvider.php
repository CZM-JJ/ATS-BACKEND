<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Use optimized queries in production
        if ($this->app->environment('production')) {
            Model::preventSilentlyFailingAttributes();
            Model::preventAccessingMissingAttributes();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Optimize for production
        if ($this->app->environment('production')) {
            // Use HTTPS URLs
            if (config('app.url')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
                \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            }
        }
    }
}

