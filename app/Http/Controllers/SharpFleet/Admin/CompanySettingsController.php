<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CompanySettingsController extends Controller
{
    /**
     * Show company settings
     */
    public function edit(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $settingsService = new CompanySettingsService(
            $user['organisation_id']
        );

        return view('sharpfleet.admin.settings', [
            'settings' => $settingsService->all(),
        ]);
    }

    /**
     * Update company settings
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $settingsService = new CompanySettingsService(
            $user['organisation_id']
        );

        $settingsService->saveFromRequest($request);

        if ($request->has('setup_finish')) {
            $settings = $settingsService->all();
            if (!isset($settings['setup']) || !is_array($settings['setup'])) {
                $settings['setup'] = [];
            }
            $settings['setup']['completed_at'] = now()->toDateTimeString();
            $settings['setup']['version'] = 1;

            DB::connection('sharpfleet')
                ->table('company_settings')
                ->updateOrInsert(
                    ['organisation_id' => $user['organisation_id']],
                    [
                        'organisation_id' => $user['organisation_id'],
                        'settings_json'   => json_encode($settings),
                    ]
                );

            return redirect('/app/sharpfleet/admin')
                ->with('success', 'Setup complete.');
        }

        if ($request->has('save_and_return')) {
            return redirect('/app/sharpfleet/admin/company')
                ->with('success', 'Company settings updated.');
        }

        return back()->with('success', 'Company settings updated.');
    }
}
