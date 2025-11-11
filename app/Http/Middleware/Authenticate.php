<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Default for web/admin guards
        if (! $request->expectsJson()) {
            return route('login'); // Or your admin login
        }

        // For customer guard: Redirect to customer login
        if (str_contains($request->route()->gatherMiddleware(), 'auth:customer')) {
            return route('customer.login');
        }

        return null; // JSON responses
    }
}