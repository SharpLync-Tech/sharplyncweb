<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\OrganisationAccount;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupWizardController extends Controller
{
    private const TOTAL_STEPS_COMPANY = 11;
    private const TOTAL_STEPS_PERSONAL = 10;

    public function accountType(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        $userFirstName = '';
        $user = $request->session()->get('sharpfleet.user');
        if (is_array($user)) {
            $userFirstName = trim((string) ($user['first_name'] ?? ''));
        }

        // Fallback to DB lookup if session is missing the name.
        if ($userFirstName === '' && is_array($user)) {
            $userId = (int) ($user['id'] ?? 0);
            if ($userId > 0
                && Schema::connection('sharpfleet')->hasTable('users')
                && Schema::connection('sharpfleet')->hasColumn('users', 'first_name')) {
                try {
                    $userFirstName = trim((string) (DB::connection('sharpfleet')
                        ->table('users')
                        ->where('id', $userId)
                        ->value('first_name') ?? ''));
                } catch (\Throwable $e) {
                    $userFirstName = '';
                }
            }
        }

        $select = ['id'];
        if (Schema::connection('sharpfleet')->hasColumn('organisations', 'account_type')) {
            $select[] = 'account_type';
        }
        if (Schema::connection('sharpfleet')->hasColumn('organisations', 'company_type')) {
            $select[] = 'company_type';
        }

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select($select)
            ->where('id', $organisationId)
            ->first();

        $selected = $this->effectiveAccountTypeFromOrganisationRow($organisation) ?: OrganisationAccount::TYPE_COMPANY;

        return view('sharpfleet.admin.setup.account-type', [
            'organisation' => $organisation,
            'selectedAccountType' => $selected,
            'userFirstName' => $userFirstName,
            'step' => 1,
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeAccountType(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if (!Schema::connection('sharpfleet')->hasColumn('organisations', 'account_type')) {
            return back()->withErrors([
                'account_type' => 'Account type requires a database update. Please run the installer SQL to add organisations.account_type, then try again.',
            ]);
        }

        $validated = $request->validate([
            'account_type' => ['required', 'in:personal,sole_trader,company'],
        ]);

        $accountType = (string) $validated['account_type'];
        $companyType = null;

        if ($accountType === OrganisationAccount::TYPE_COMPANY) {
            $companyType = OrganisationAccount::TYPE_COMPANY;
        } elseif ($accountType === OrganisationAccount::TYPE_SOLE_TRADER) {
            $companyType = OrganisationAccount::TYPE_SOLE_TRADER;
        }

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->update([
                'account_type' => $accountType,
                // Keep legacy column in sync where possible.
                'company_type' => $companyType,
                'updated_at' => now(),
            ]);

        if ($accountType === OrganisationAccount::TYPE_PERSONAL) {
            $settings = $this->loadSettings($organisationId);

            $settings['customer']['enabled'] = false;
            $settings['customer']['allow_select'] = false;
            $settings['customer']['allow_manual'] = false;

            $this->persistSettings($organisationId, $settings);
        }

        return redirect('/app/sharpfleet/admin/setup/company')
            ->with('success', 'Account type saved. Next: company details.');
    }

    /**
     * Step 2: Company details
     */
    public function company(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }

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
            'step' => $this->wizardStepForOrganisation($organisationId, 'company'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    /**
     * Persist Step 1 then go to settings.
     */
    public function storeCompany(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }

        $accountType = OrganisationAccount::forOrganisationId($organisationId);

        $rules = [
            'timezone' => ['required', 'string'],
            'industry' => ['nullable', 'string', 'max:255'],
        ];

        // Only company accounts need to explicitly provide an organisation name.
        $rules['company_name'] = ($accountType === OrganisationAccount::TYPE_COMPANY)
            ? ['required', 'string', 'max:255']
            : ['nullable', 'string', 'max:255'];

        $validated = $request->validate($rules);

        $timezone = (string) $validated['timezone'];
        if (!in_array($timezone, $this->auNzTimezones(), true)) {
            return back()->withErrors(['timezone' => 'Please choose a valid Australia/New Zealand time zone.'])->withInput();
        }

        $nameToSave = trim((string) ($validated['company_name'] ?? ''));

        if ($accountType !== OrganisationAccount::TYPE_COMPANY) {
            // For personal/sole trader, avoid asking again: default to the user's name if the org name is still the placeholder.
            $user = $request->session()->get('sharpfleet.user');
            $fullName = '';
            if (is_array($user)) {
                $first = trim((string) ($user['first_name'] ?? ''));
                $last = trim((string) ($user['last_name'] ?? ''));
                $fullName = trim((string) ($first . ' ' . $last));

                // Fallback to DB lookup if session doesn't have names.
                if ($fullName === '') {
                    $userId = (int) ($user['id'] ?? 0);
                    if ($userId > 0
                        && Schema::connection('sharpfleet')->hasTable('users')
                        && Schema::connection('sharpfleet')->hasColumn('users', 'first_name')
                        && Schema::connection('sharpfleet')->hasColumn('users', 'last_name')) {
                        try {
                            $row = DB::connection('sharpfleet')
                                ->table('users')
                                ->select('first_name', 'last_name')
                                ->where('id', $userId)
                                ->first();

                            $first = trim((string) ($row->first_name ?? ''));
                            $last = trim((string) ($row->last_name ?? ''));
                            $fullName = trim((string) ($first . ' ' . $last));
                        } catch (\Throwable $e) {
                            $fullName = '';
                        }
                    }
                }
            }

            $currentName = (string) (DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', $organisationId)
                ->value('name') ?? '');

            $isPlaceholder = trim($currentName) === '' || trim($currentName) === 'Company';
            if ($isPlaceholder && $fullName !== '') {
                $nameToSave = $fullName;
            }
        }

        if ($nameToSave !== '') {
            DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', $organisationId)
                ->update([
                    'name' => $nameToSave,
                    'updated_at' => now(),
                ]);
        }

        $settings = $this->loadSettings($organisationId);
        $settings['timezone'] = $timezone;

        if ($accountType === OrganisationAccount::TYPE_PERSONAL) {
            $settings['industry'] = '';
        } else {
            $settings['industry'] = trim((string) ($validated['industry'] ?? ''));
        }
        $this->persistSettings($organisationId, $settings);

        return redirect('/app/sharpfleet/admin/setup/settings/presence')
            ->with('success', 'Company details saved. Next: passenger/client presence.');
    }

    /**
     * Step 2: Passenger / Client presence
     */
    public function settingsPresence(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.presence', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'presence'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsPresence(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }

        $validated = $request->validate([
            'client_label' => ['nullable', 'string', 'max:50'],
        ]);

        $settings = $this->loadSettings($organisationId);

        $settings['client_presence']['enabled'] = $request->boolean('enable_client_presence');
        $settings['client_presence']['required'] = $request->boolean('require_client_presence');
        $settings['client_presence']['label'] = trim((string) ($validated['client_label'] ?? 'Client'));

        $this->persistSettings($organisationId, $settings);

        if (!OrganisationAccount::wizardIncludesCustomerStep($organisationId)) {
            return redirect('/app/sharpfleet/admin/setup/settings/trip-rules')
                ->with('success', 'Saved. Next: trip rules.');
        }

        return redirect('/app/sharpfleet/admin/setup/settings/customer')
            ->with('success', 'Saved. Next: customer/client capture.');
    }

    /**
     * Step 3: Customer / Client capture
     */
    public function settingsCustomer(Request $request)
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }

        if (!OrganisationAccount::wizardIncludesCustomerStep($organisationId)) {
            return redirect('/app/sharpfleet/admin/setup/settings/trip-rules');
        }

        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.customer', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'customer'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsCustomer(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }

        if (!OrganisationAccount::wizardIncludesCustomerStep($organisationId)) {
            return redirect('/app/sharpfleet/admin/setup/settings/trip-rules');
        }

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

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.trip_rules', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'trip_rules'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsTripRules(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
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

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.vehicle_tracking', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'vehicle_tracking'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsVehicleTracking(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
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

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.reminders', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'reminders'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsReminders(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
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

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.client_addresses', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'client_addresses'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsClientAddresses(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
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

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.safety_check', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'safety_check'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsSafetyCheck(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
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

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.settings.incident_reporting', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'incident_reporting'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    public function storeSettingsIncidentReporting(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
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

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }
        $settings = $this->loadSettings($organisationId);

        return view('sharpfleet.admin.setup.finish', [
            'settings' => $settings,
            'step' => $this->wizardStepForOrganisation($organisationId, 'finish'),
            'totalSteps' => $this->wizardTotalStepsForOrganisation($organisationId),
        ]);
    }

    /**
     * Step 2 completion: mark setup complete.
     */
    public function finish(Request $request): RedirectResponse
    {
        $organisationId = $this->requireAdminOrganisationId($request);

        if ($redirect = $this->redirectIfAccountTypeMissing($organisationId)) {
            return $redirect;
        }

        $accountType = OrganisationAccount::forOrganisationId($organisationId);
        if ($accountType === OrganisationAccount::TYPE_PERSONAL) {
            $fleetUser = $request->session()->get('sharpfleet.user');
            $userId = is_array($fleetUser) ? (int) ($fleetUser['id'] ?? 0) : 0;

            if ($userId > 0 && Schema::connection('sharpfleet')->hasColumn('users', 'is_driver')) {
                $updates = [
                    'is_driver' => 1,
                ];
                if (Schema::connection('sharpfleet')->hasColumn('users', 'updated_at')) {
                    $updates['updated_at'] = now();
                }

                DB::connection('sharpfleet')
                    ->table('users')
                    ->where('id', $userId)
                    ->update($updates);

                // Ensure driver access works immediately without a re-login.
                $request->session()->put('sharpfleet.user.is_driver', 1);
                $request->session()->put('sharpfleet.driver_id', $userId);
            }
        }

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

        return redirect('/app/sharpfleet/admin/setup/account-type')
            ->with('success', 'Setup wizard reset.');
    }

    private function redirectIfAccountTypeMissing(int $organisationId): ?RedirectResponse
    {
        if (!Schema::connection('sharpfleet')->hasColumn('organisations', 'account_type')) {
            return null;
        }

        $accountType = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->value('account_type');

        if (empty($accountType)) {
            return redirect('/app/sharpfleet/admin/setup/account-type');
        }

        return null;
    }

    private function wizardTotalStepsForOrganisation(int $organisationId): int
    {
        return OrganisationAccount::wizardIncludesCustomerStep($organisationId)
            ? self::TOTAL_STEPS_COMPANY
            : self::TOTAL_STEPS_PERSONAL;
    }

    private function wizardStepForOrganisation(int $organisationId, string $key): int
    {
        $includesCustomer = OrganisationAccount::wizardIncludesCustomerStep($organisationId);

        $steps = $includesCustomer
            ? [
                'account_type' => 1,
                'company' => 2,
                'presence' => 3,
                'customer' => 4,
                'trip_rules' => 5,
                'vehicle_tracking' => 6,
                'reminders' => 7,
                'client_addresses' => 8,
                'safety_check' => 9,
                'incident_reporting' => 10,
                'finish' => 11,
            ]
            : [
                'account_type' => 1,
                'company' => 2,
                'presence' => 3,
                // customer omitted
                'trip_rules' => 4,
                'vehicle_tracking' => 5,
                'reminders' => 6,
                'client_addresses' => 7,
                'safety_check' => 8,
                'incident_reporting' => 9,
                'finish' => 10,
            ];

        return (int) ($steps[$key] ?? 1);
    }

    private function effectiveAccountTypeFromOrganisationRow($organisation): ?string
    {
        if (!$organisation) {
            return null;
        }

        $accountType = strtolower(trim((string) ($organisation->account_type ?? '')));
        if (in_array($accountType, [OrganisationAccount::TYPE_PERSONAL, OrganisationAccount::TYPE_SOLE_TRADER, OrganisationAccount::TYPE_COMPANY], true)) {
            return $accountType;
        }

        $companyType = strtolower(trim((string) ($organisation->company_type ?? '')));
        if ($companyType === OrganisationAccount::TYPE_SOLE_TRADER) {
            return OrganisationAccount::TYPE_SOLE_TRADER;
        }

        if ($companyType === OrganisationAccount::TYPE_COMPANY) {
            return OrganisationAccount::TYPE_COMPANY;
        }

        return null;
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
