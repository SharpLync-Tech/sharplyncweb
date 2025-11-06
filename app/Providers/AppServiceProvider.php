<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Mail\MailManager;
use App\Services\GraphMailService;

class AppServiceProvider extends ServiceProvider
{
    /**
     *  Register any application services.
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

        // ✅ Register custom Microsoft Graph mailer
        $this->app->resolving(MailManager::class, function ($manager) {
            $manager->extend('graph', function ($config) {
                return app(GraphMailService::class);
            });
        });
    }
}