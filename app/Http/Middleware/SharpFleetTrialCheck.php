<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SharpFleetTrialCheck
{
    public function handle(Request $request, Closure $next)
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            return redirect('/app/sharpfleet/admin/login');
        }

        // Check if user is in trial period
        $trialEndsAt = $this->getUserTrialEndDate($user);

        if ($trialEndsAt && Carbon::now()->isAfter($trialEndsAt)) {
            // Trial has expired - restrict functionality
            // For now, we'll allow all functionality but show a warning
            // In production, this would restrict access

            $currentRoute = $request->route();

            if ($currentRoute) {
                $routeName = $currentRoute->getName();
                $uri = $request->getRequestUri();

                // For demo purposes, just add a warning but allow access
                // In production, restrict routes here
                $request->merge(['trial_expired' => true]);
            }
        }

        return $next($request);
    }

    private function getUserTrialEndDate(array $user): ?Carbon
    {
        // For demo purposes, trial never expires
        // In production, this would check database columns
        return Carbon::now()->addDays(30);
    }
}