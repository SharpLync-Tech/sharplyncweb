<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupWizardController extends Controller
{
    private const TOTAL_STEPS = 10;

    /**
     * Step 1: Company details
     */
    public function company(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        $settings['trip']['purpose_of_travel_enabled'] = $request->boolean('enable_purpose_of_travel');
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
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    /**
     * Persist Step 1 then go to settings.
     */
    public function storeCompany(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

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

        $settings = $this->loadSettings($organisationId);
        $settings['timezone'] = $timezone;
        $settings['industry'] = trim((string) ($validated['industry'] ?? ''));
        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/presence')
            ->with('success', 'Step 1 saved. Next: passenger/client presence.');
    }

    /**
     * Step 2: Passenger / Client presence
     */
    public function settingsPresence(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.presence', [
            'settings' => $settings,
            'step' => 2,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsPresence(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        $validated = $request->validate([
            'client_label' => ['nullable', 'string', 'max:50'],
        ]);

        $settings = $this->loadSettings($organisationId);

        $settings['client_presence']['enabled'] = $request->boolean('enable_client_presence');
        $settings['client_presence']['required'] = $request->boolean('require_client_presence');
        $settings['client_presence']['label'] = trim((string) ($validated['client_label'] ?? 'Client'));

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/customer')
            ->with('success', 'Step 2 saved. Next: customer/client capture.');
    }

    /**
     * Step 3: Customer / Client capture
     */
    public function settingsCustomer(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.customer', [
            'settings' => $settings,
            'step' => 3,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsCustomer(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        $settings['customer']['enabled'] = $request->boolean('enable_customer_capture');
        $settings['customer']['allow_select'] = $request->boolean('allow_customer_select');
        $settings['customer']['allow_manual'] = $request->boolean('allow_customer_manual');

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/trip-rules')
            ->with('success', 'Step 3 saved. Next: trip rules.');
    }

    /**
    * Step 4: Trip rules
     */
    public function settingsTripRules(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.trip_rules', [
            'settings' => $settings,
            'step' => 4,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsTripRules(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        $settings['trip']['odometer_required'] = $request->boolean('require_odometer_start');
        $settings['trip']['odometer_allow_override'] = $request->boolean('allow_odometer_override');
        $settings['trip']['allow_private_trips'] = $request->boolean('allow_private_trips');
        $settings['trip']['require_manual_start_end_times'] = $request->boolean('require_manual_start_end_times');

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/vehicle-tracking')
            ->with('success', 'Step 4 saved. Next: vehicle tracking.');
    }

    /**
    * Step 5: Vehicle tracking
     */
    public function settingsVehicleTracking(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.vehicle_tracking', [
            'settings' => $settings,
            'step' => 5,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsVehicleTracking(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        $settings['vehicles']['registration_tracking_enabled'] = $request->boolean('enable_vehicle_registration_tracking');
        $settings['vehicles']['servicing_tracking_enabled'] = $request->boolean('enable_vehicle_servicing_tracking');

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/reminders')
            ->with('success', 'Step 5 saved. Next: reminder emails.');
    }

    /**
    * Step 6: Reminder emails thresholds
     */
    public function settingsReminders(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.reminders', [
            'settings' => $settings,
            'step' => 6,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsReminders(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $validated = $request->validate([
            'reminder_registration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'reminder_service_days' => ['required', 'integer', 'min:1', 'max:365'],
            'reminder_service_reading_threshold' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $settings = $this->loadSettings($organisationId);
        $settings['reminders']['registration_days'] = (int) $validated['reminder_registration_days'];
        $settings['reminders']['service_days'] = (int) $validated['reminder_service_days'];
        $settings['reminders']['service_reading_threshold'] = (int) $validated['reminder_service_reading_threshold'];

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/client-addresses')
            ->with('success', 'Step 6 saved. Next: client address tracking.');
    }

    /**
    * Step 7: Client address tracking
     */
    public function settingsClientAddresses(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.client_addresses', [
            'settings' => $settings,
            'step' => 7,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsClientAddresses(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        $settings['client_presence']['enable_addresses'] = $request->boolean('enable_client_addresses');
        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/safety-check')
            ->with('success', 'Step 7 saved. Next: pre-drive safety check.');
    }

    /**
    * Step 8: Safety check
     */
    public function settingsSafetyCheck(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.safety_check', [
            'settings' => $settings,
            'step' => 8,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsSafetyCheck(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        $settings['safety_check']['enabled'] = $request->boolean('enable_safety_check');
        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/incident-reporting')
            ->with('success', 'Step 8 saved. Next: vehicle issue/accident reporting.');
    }

    /**
    * Step 9: Vehicle issue / accident reporting
     */
    public function settingsIncidentReporting(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.incident_reporting', [
            'settings' => $settings,
            'step' => 9,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    public function storeSettingsIncidentReporting(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        $settings['faults']['enabled'] = $request->boolean('enable_fault_reporting');
        $settings['faults']['allow_during_trip'] = $request->boolean('allow_fault_during_trip');
        $settings['faults']['require_end_of_trip_check'] = $request->boolean('require_end_of_trip_fault_check');

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/finish')
            ->with('success', 'Step 9 saved. Final step: finish setup.');
    }

    /**
    * Step 10: Finish setup (review + complete)
     */
    public function finishView(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.finish', [
            'settings' => $settings,
            'step' => 10,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    /**
     * Step 2 completion: mark setup complete.
     */
    public function finish(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        if (!isset($settings['setup']) || !is_array($settings['setup'])) {
            $settings['setup'] = [];
        }

        $settings['setup']['completed_at'] = now()->toDateTimeString();
        $settings['setup']['version'] = 1;

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin')
            ->with('success', 'Setup complete.');
    }

    /**
     * Reset the setup completion flag so the wizard can be re-run (admin only).
     */
    public function rerun(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);
        $settings = $this->loadSettings($organisationId);

        if (!isset($settings['setup']) || !is_array($settings['setup'])) {
            $settings['setup'] = [];
        }

        $settings['setup']['completed_at'] = null;

        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/company')
            ->with('success', 'Setup wizard reset.');
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

    private function requireAdminOrganisationId(Request $request): int
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'Invalid organisation');
        }

        return $organisationId;
    }

    private function loadSettings(int $organisationId): array
    {
        $settingsService = new CompanySettingsService($organisationId);
        return $settingsService->all();
    }

    private function persistSettings(int $organisationId, array $settings): void
    {
        DB::connection('sharpfleet')
            ->table('company_settings')
            ->updateOrInsert(
                ['organisation_id' => $organisationId],
                [
                    'organisation_id' => $organisationId,
                    'settings_json' => json_encode($settings),
                ]
            );
    }
}
