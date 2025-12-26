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
            $currentRoute = $request->route();

            if ($currentRoute) {
                $routeName = $currentRoute->getName();
                $uri = $request->getRequestUri();

                // Allow login, logout, and reports
                $allowedRoutes = [
                    'sharpfleet.admin.login',
                    'sharpfleet.admin.logout',
                    'sharpfleet.admin.reports.index',
                    'sharpfleet.admin.reports.vehicles',
                    'sharpfleet.admin.reports.trips',
                ];

                $allowedUris = [
                    '/app/sharpfleet/admin/login',
                    '/app/sharpfleet/admin/logout',
                    '/app/sharpfleet/admin/reports',
                ];

                // Check if current route/URI is allowed
                if (!in_array($routeName, $allowedRoutes) &&
                    !str_contains($uri, '/reports') &&
                    !str_contains($uri, '/logout')) {

                    // Redirect to trial expired page
                    return redirect('/app/sharpfleet/admin/trial-expired')
                        ->with('warning', 'Your trial has expired. Please upgrade to continue using SharpFleet.');
                }
            }
        }

        return $next($request);
    }

    private function getUserTrialEndDate(array $user): ?Carbon
    {
        try {
            // Check user table for trial_ends_at
            $userRecord = \DB::connection('sharpfleet')
                ->table('users')
                ->where('id', $user['id'])
                ->first();

            if ($userRecord && $userRecord->trial_ends_at) {
                return Carbon::parse($userRecord->trial_ends_at);
            }

            // Fallback to organisation trial end date
            $orgRecord = \DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', $user['organisation_id'])
                ->first();

            if ($orgRecord && $orgRecord->trial_ends_at) {
                return Carbon::parse($orgRecord->trial_ends_at);
            }

            // If no trial data found, assume trial has expired (for existing users)
            return Carbon::now()->subDay();

        } catch (\Exception $e) {
            // If database query fails, allow access (fail open)
            return Carbon::now()->addDays(30);
        }
    }
}