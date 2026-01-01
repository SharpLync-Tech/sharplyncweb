<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /**
         * ------------------------------------------------------------
         * CSRF protection
         * ------------------------------------------------------------
         * Enabled globally, with Stripe webhook explicitly excluded.
         * Required for Laravel 12 + external POSTs (Stripe).
         */
        $middleware->validateCsrfTokens(except: [
            'app/sharpfleet/stripe/webhook',
        ]);

        /**
         * ------------------------------------------------------------
         * Global middleware
         * ------------------------------------------------------------
         */
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        /**
         * ------------------------------------------------------------
         * Route middleware aliases
         * ------------------------------------------------------------
         */
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
        ]);
    })
    ->withProviders([
        \App\Providers\MenuServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
