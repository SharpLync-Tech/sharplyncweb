<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CompanySettingsController extends Controller
{
    protected CompanySettingsService $settingsService;

    public function __construct(CompanySettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function edit()
    {
        $organisationId = session('sharpfleet.user.organisation_id');

        $settings = $this->settingsService->getSettings($organisationId);

        return view('sharpfleet.admin.company-settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organisationId = session('sharpfleet.user.organisation_id');

        $this->settingsService->saveSettings(
            $organisationId,
            $request->all()
        );

        // Decide where to go next
        if ($request->has('save_and_return')) {
            return redirect('/app/sharpfleet/admin/company')
                ->with('success', 'Company settings updated.');
        }

        return back()->with('success', 'Company settings updated.');
    }
}
