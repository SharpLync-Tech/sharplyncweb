<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupWizardController extends Controller
{
    /**
     * Step 1: Company details
     */
    public function company(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        $settingsService = new CompanySettingsService($organisationId);
        $settings = $settingsService->all();

        return view('sharpfleet.admin.setup.company', [
            'organisation' => $organisation,
            'settings' => $settings,
            'timezones' => $this->auNzTimezones(),
        ]);
    }

    /**
     * Persist Step 1 then go to settings.
     */
    public function storeCompany(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string'],
            'industry' => ['nullable', 'string', 'max:255'],
        ]);

        $timezone = (string) $validated['timezone'];
        if (!in_array($timezone, $this->auNzTimezones(), true)) {
            return back()->withErrors(['timezone' => 'Please choose a valid Australia/New Zealand time zone.'])->withInput();
        }

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->update([
                'name' => trim($validated['company_name']),
                'updated_at' => now(),
            ]);

        // Persist timezone + industry into company_settings.settings_json without changing schema.
        $settingsService = new CompanySettingsService($organisationId);
        $settings = $settingsService->all();

        $settings['timezone'] = $timezone;
        $settings['industry'] = trim((string) ($validated['industry'] ?? ''));

        DB::connection('sharpfleet')
            ->table('company_settings')
            ->updateOrInsert(
                ['organisation_id' => $organisationId],
                [
                    'organisation_id' => $organisationId,
                    'settings_json' => json_encode($settings),
                ]
            );

        return redirect('/app/sharpfleet/admin/settings')
            ->with('success', 'Step 1 saved. Now configure company settings.');
    }

    /**
     * Step 2 completion: mark setup complete.
     */
    public function finish(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $settingsService = new CompanySettingsService($organisationId);
        $settings = $settingsService->all();

        if (!isset($settings['setup']) || !is_array($settings['setup'])) {
            $settings['setup'] = [];
        }

        $settings['setup']['completed_at'] = now()->toDateTimeString();
        $settings['setup']['version'] = 1;

        DB::connection('sharpfleet')
            ->table('company_settings')
            ->updateOrInsert(
                ['organisation_id' => $organisationId],
                [
                    'organisation_id' => $organisationId,
                    'settings_json' => json_encode($settings),
                ]
            );

        return redirect('/app/sharpfleet/admin')
            ->with('success', 'Setup complete.');
    }

    private function auNzTimezones(): array
    {
        return [
            // Australia
            'Australia/Brisbane',
            'Australia/Sydney',
            'Australia/Melbourne',
            'Australia/Hobart',
            'Australia/Adelaide',
            'Australia/Darwin',
            'Australia/Perth',
            'Australia/Eucla',

            // New Zealand
            'Pacific/Auckland',
            'Pacific/Chatham',
        ];
    }
}
