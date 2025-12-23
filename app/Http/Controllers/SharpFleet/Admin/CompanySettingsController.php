<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanySettingsController extends Controller
{
    /**
     * Show the company settings form
     */
    public function edit(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $settingsService = new CompanySettingsService($user['organisation_id']);

        return view('sharpfleet.admin.settings', [
            'settings' => $settingsService->all(),
        ]);
    }

    /**
     * Update company settings
     */
    public function update(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        // Load existing settings
        $settingsService = new CompanySettingsService($user['organisation_id']);
        $currentSettings = $settingsService->all();

        /*
         * We MERGE updates into existing JSON
         * (Option A – safe, future-proof)
         */
        $updatedSettings = array_replace_recursive($currentSettings, [
            'client_presence' => [
                'enabled'  => $request->boolean('client_presence_enabled'),
                'required' => $request->boolean('client_presence_required'),
                'label'    => $request->input('client_presence_label', 'Client'),
            ],

            'trip' => [
                'odometer_required'        => $request->boolean('odometer_required'),
                'odometer_allow_override'  => $request->boolean('odometer_allow_override'),
            ],

            'safety_check' => [
                'enabled' => $request->boolean('safety_check_enabled'),
            ],
        ]);

        // Persist settings
        DB::connection('sharpfleet')
            ->table('company_settings')
            ->updateOrInsert(
                ['organisation_id' => $user['organisation_id']],
                ['settings_json' => json_encode($updatedSettings)]
            );

        return redirect('/app/sharpfleet/admin/settings')
            ->with('success', 'Company settings saved successfully');
    }
}
