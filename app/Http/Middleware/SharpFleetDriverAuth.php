<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SharpFleetDriverAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('sharpfleet.user');

        // Not logged into SharpFleet
        if (!$user) {
            return redirect('/app/sharpfleet/login');
        }

        // Logged in but not a driver
        // Allow driver or admin (for sole traders/admins who drive)
        if (!in_array($user['role'] ?? null, ['driver', 'admin'])) {
            return response()->view('sharpfleet.errors.driver-denied', [], 403);
        }

        return $next($request);
    }
}
