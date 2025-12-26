<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SharpFleetAdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        // Not logged into SharpFleet at all
        if (!$fleetUser || empty($fleetUser['logged_in'])) {
            return redirect('/app/sharpfleet/login');
        }

        // Logged in, but not an admin
        if (($fleetUser['role'] ?? null) !== 'admin') {
            abort(403, 'SharpFleet admin access only.');
        }

        return $next($request);
    }
}
