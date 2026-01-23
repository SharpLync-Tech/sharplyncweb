<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
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

        // Replace the default CSRF middleware with our SharpFleet-aware version.
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        /**
         * ------------------------------------------------------------
         * Global middleware
         * ------------------------------------------------------------
         */
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // 🔐 If you're using cookie-based auth for web SPA clients, enable this:
        // $middleware->prepend(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);

        /**
         * ------------------------------------------------------------
         * Route middleware aliases
         * ------------------------------------------------------------
         */
        $middleware->alias([
            'admin.auth'    => \App\Http\Middleware\AdminAuth::class,
            'auth'          => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth:sanctum'  => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'api.key'       => \App\Http\Middleware\ApiKeyAuth::class,
        ]);
    })
    ->withProviders([
        \App\Providers\MenuServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
