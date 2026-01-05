<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
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

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $settingsService = new CompanySettingsService($organisationId);

        $driversCount = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where(function ($q) {
                $q
                    ->where(function ($qq) {
                        $qq
                            ->where('role', 'driver')
                            ->where(function ($q2) {
                                $q2->whereNull('is_driver')->orWhere('is_driver', 1);
                            });
                    })
                    ->orWhere(function ($qq) {
                        $qq
                            ->where('role', 'admin')
                            ->where('is_driver', 1);
                    });
            })
            ->count();

        $vehiclesCount = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        return view('sharpfleet.admin.company', [
            'companyName'           => $organisation->name,
            'companyType'           => $organisation->company_type
                                        ? ucfirst(str_replace('_', ' ', $organisation->company_type))
                                        : '—',
            'industry'              => $organisation->industry ?: '—',
            'timezone'              => $settingsService->timezone(),
            'driversCount'          => $driversCount,
            'vehiclesCount'         => $vehiclesCount,
            'safetyChecksEnabled'   => $settingsService->safetyCheckEnabled(),
            'clientPresenceEnabled' => $settingsService->clientPresenceEnabled(),
        ]);
    }
}
