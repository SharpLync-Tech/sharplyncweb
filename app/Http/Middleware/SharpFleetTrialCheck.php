<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SharpFleet\EntitlementService;

class SharpFleetTrialCheck
{
    public function handle(Request $request, Closure $next)
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            return redirect('/app/sharpfleet/admin/login');
        }

        $entitlements = new EntitlementService($user);

        if ($entitlements->isTrialExpired()) {
            // Preserve existing behavior: only enforce restrictions when we have a matched route.
            // (Avoid changing behavior for non-matched/invalid routes.)
            if ($request->route() && !$entitlements->canAccessRequest($request)) {
                return redirect('/app/sharpfleet/admin/trial-expired')
                    ->with('warning', 'Your trial has expired. Please upgrade to continue using SharpFleet.');
            }
        }

        return $next($request);
    }
}
