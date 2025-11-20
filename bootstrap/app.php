<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Route middleware aliases
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    // 🔥 Correct place to register your provider in Laravel 12
    ->afterBootstrapping(\Illuminate\Foundation\Bootstrap\RegisterProviders::class, 
        function ($app) {
            $app->register(\App\Providers\MenuServiceProvider::class);
        }
    )

    ->create();
