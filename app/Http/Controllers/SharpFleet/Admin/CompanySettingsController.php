<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CompanySettingsController extends Controller
{
    /**
     * Show company settings
     */
    public function edit(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canManageCompanySettings($user)) {
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

        if (!$user || !Roles::canManageCompanySettings($user)) {
            abort(403, 'Admin access only');
        }

        $settingsService = new CompanySettingsService(
            $user['organisation_id']
        );

        $settingsService->saveFromRequest($request);

        if ($request->has('save_and_return')) {
            return redirect('/app/sharpfleet/admin/company')
                ->with('success', 'Company settings updated.');
        }

        return back()->with('success', 'Company settings updated.');
    }
}
