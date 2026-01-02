<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharpFleetSetupWizard
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->session()->get('sharpfleet.user');

        // Only applies to authenticated admins.
        if (!$user || ($user['role'] ?? null) !== 'admin') {
            return $next($request);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            return $next($request);
        }

        // Allow wizard pages + settings page while onboarding.
        $path = ltrim($request->path(), '/');
        if (
            str_starts_with($path, 'app/sharpfleet/admin/setup') ||
            $path === 'app/sharpfleet/admin/settings'
        ) {
            return $next($request);
        }

        // Check completion flag in company_settings.settings_json
        $row = DB::connection('sharpfleet')
            ->table('company_settings')
            ->select('settings_json')
            ->where('organisation_id', $organisationId)
            ->first();

        $settings = [];
        if ($row && !empty($row->settings_json)) {
            $decoded = json_decode($row->settings_json, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        $completedAt = $settings['setup']['completed_at'] ?? null;
        if (empty($completedAt)) {
            return redirect('/app/sharpfleet/admin/setup/company');
        }

        return $next($request);
    }
}
