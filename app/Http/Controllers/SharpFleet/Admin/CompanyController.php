<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Company overview / dashboard
     */
    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        // ---- Organisation (identity) ----
        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        // ---- Settings (via service, never touch JSON directly) ----
        $settingsService = new CompanySettingsService($organisationId);

        // ---- Counts ----
        $driversCount = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('role', 'driver')
            ->count();

        $vehiclesCount = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->count();

        return view('sharpfleet.admin.company', [
            'organisationId'        => $organisationId,
            'companyName'           => $organisation->name,
            'companyType'           => '—', // Step 3
            'industry'              => '—', // Step 3
            'timezone'              => $settingsService->timezone(),
            'driversCount'          => $driversCount,
            'vehiclesCount'         => $vehiclesCount,
            'safetyChecksEnabled'   => $settingsService->safetyCheckEnabled(),
            'clientPresenceEnabled' => $settingsService->clientPresenceEnabled(),
        ]);
    }
}
