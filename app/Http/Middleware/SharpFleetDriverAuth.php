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
        if (($user['role'] ?? null) !== 'driver') {
            abort(403, 'Driver access only.');
        }

        return $next($request);
    }
}
