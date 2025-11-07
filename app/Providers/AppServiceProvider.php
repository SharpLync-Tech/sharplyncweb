<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

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
        // ✅ Force HTTPS for all routes and assets in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // ✅ Register custom Microsoft Graph mailer transport
        Mail::extend('graph', function (array $config) {
            return new \App\Mail\Transport\GraphTransport();
        });
    }
}
