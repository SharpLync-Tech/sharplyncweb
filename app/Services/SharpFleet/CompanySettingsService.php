<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CompanySettingsService
{
    /**
     * Organisation context
     */
    protected int $organisationId;

    /**
     * Default settings (used if DB row or keys are missing)
     */
    protected array $defaults = [
        'timezone'    => 'Australia/Brisbane',
        'date_format' => 'd/m/Y',
        'time_format' => 'H:i',

        'vehicles' => [
            'registration_tracking_enabled' => false,
            'servicing_tracking_enabled'    => false,
        ],

        'trip' => [
            'odometer_required'                => true,
            'odometer_autofill_from_last_trip' => true,
            'odometer_allow_override'          => true,
            'trip_type_enabled'                => true,
            'trip_type_required'               => true,
            'allow_private_trips'               => false,
            'require_manual_start_end_times'   => false,
        ],

        'client_presence' => [
            'enabled'                      => false,
            'required'                     => false,
            'label'                        => 'Client',
            'require_details_when_present' => false,
            'enable_addresses'             => false,
        ],

        // Customer / client capture (never blocks trip start)
        'customer' => [
            'enabled'       => false,
            'allow_select'  => true,
            'allow_manual'  => true,
        ],

        'safety_check' => [
            'enabled'  => false,
            'required' => false,
            'items'    => [],
        ],

        'faults' => [
            'enabled'                  => false,
            'allow_during_trip'         => true,
            'require_end_of_trip_check' => false,
        ],

        // Reporting preferences (used by SharpFleet admin reports)
        // These defaults preserve current behaviour unless a subscriber overrides them in settings_json.
        'reporting' => [
            // Whether trips with trip_mode='private' should be included in reports.
            'include_private_trips' => true,

            // If false, the reporting page will show applied settings but will not allow changing filters.
            'allow_overrides' => true,

            // Granular overrides (only used when allow_overrides=true)
            'allow_date_override' => true,
            'allow_vehicle_override' => true,
            'allow_customer_override' => true,

            // Default date range rule used when overrides are not provided/allowed.
            // Supported: month_to_date, last_30_days
            'default_date_range' => 'month_to_date',

            // Optional guardrail for manual date ranges when overrides are allowed.
            // Null means no maximum.
            'max_date_range_days' => null,

            // Optional locked defaults when overrides are disabled.
            'default_vehicle_id' => null,
            'default_customer_id' => null,
        ],
    ];

    /**
     * Cached merged settings
     */
    protected array $settings;

    /**
     * Load settings for an organisation
     */
    public function __construct(int $organisationId)
    {
        $this->organisationId = $organisationId;
        $this->settings       = $this->loadSettings($organisationId);
    }

    /**
     * Fetch + merge settings
     */
    protected function loadSettings(int $organisationId): array
    {
        $row = DB::connection('sharpfleet')
            ->table('company_settings')
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$row) {
            return $this->defaults;
        }

        $dbSettings = json_decode($row->settings_json, true) ?? [];

        return array_replace_recursive($this->defaults, $dbSettings);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (use these everywhere – never touch JSON directly)
    |--------------------------------------------------------------------------
    */

    public function timezone(): string
    {
        return $this->settings['timezone'];
    }

    public function dateFormat(): string
    {
        return $this->settings['date_format'];
    }

    public function timeFormat(): string
    {
        return $this->settings['time_format'];
    }

    // ---- Trip rules ----

    public function odometerRequired(): bool
    {
        return (bool) $this->settings['trip']['odometer_required'];
    }

    public function odometerAutofillEnabled(): bool
    {
        return (bool) ($this->settings['trip']['odometer_autofill_from_last_trip'] ?? true);
    }

    public function odometerAllowOverride(): bool
    {
        return (bool) $this->settings['trip']['odometer_allow_override'];
    }

    public function tripTypeEnabled(): bool
    {
        return (bool) $this->settings['trip']['trip_type_enabled'];
    }

    public function tripTypeRequired(): bool
    {
        return (bool) $this->settings['trip']['trip_type_required'];
    }

    public function allowPrivateTrips(): bool
    {
        return (bool) filter_var(
            $this->settings['trip']['allow_private_trips'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function requireManualStartEndTimes(): bool
    {
        return (bool) filter_var(
            $this->settings['trip']['require_manual_start_end_times'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    // ---- Vehicles ----

    public function vehicleRegistrationTrackingEnabled(): bool
    {
        return (bool) ($this->settings['vehicles']['registration_tracking_enabled'] ?? false);
    }

    public function vehicleServicingTrackingEnabled(): bool
    {
        return (bool) ($this->settings['vehicles']['servicing_tracking_enabled'] ?? false);
    }

    // ---- Client presence ----

    public function clientPresenceEnabled(): bool
    {
        return (bool) $this->settings['client_presence']['enabled'];
    }

    public function clientPresenceRequired(): bool
    {
        return (bool) $this->settings['client_presence']['required'];
    }

    public function clientLabel(): string
    {
        return $this->settings['client_presence']['label'] ?? 'Client';
    }

    public function clientAddressesEnabled(): bool
    {
        return (bool) $this->settings['client_presence']['enable_addresses'];
    }

    // ---- Safety check ----

    public function safetyCheckEnabled(): bool
    {
        return (bool) $this->settings['safety_check']['enabled'];
    }

    public function safetyCheckRequired(): bool
    {
        return (bool) $this->settings['safety_check']['required'];
    }

    public function safetyCheckItems(): array
    {
        return $this->settings['safety_check']['items'] ?? [];
    }

    // ---- Faults ----

    public function allowFaultsDuringTrip(): bool
    {
        return (bool) $this->settings['faults']['allow_during_trip'];
    }

    public function faultsEnabled(): bool
    {
        return (bool) ($this->settings['faults']['enabled'] ?? false);
    }

    public function requireEndOfTripFaultCheck(): bool
    {
        return (bool) $this->settings['faults']['require_end_of_trip_check'];
    }

    /**
     * Persist settings updated via the admin settings UI
     */
    public function saveFromRequest(Request $request): void
    {
        $settings = $this->settings;

        // ---- Trip rules ----
        $settings['trip']['odometer_required']
            = $request->boolean('require_odometer_start');

        $settings['trip']['odometer_allow_override']
            = $request->boolean('allow_odometer_override');

        $settings['trip']['allow_private_trips']
            = $request->boolean('allow_private_trips');

        $settings['trip']['require_manual_start_end_times']
            = $request->boolean('require_manual_start_end_times');

        // ---- Client presence ----
        $settings['client_presence']['enabled']
            = $request->boolean('enable_client_presence');

        $settings['client_presence']['required']
            = $request->boolean('require_client_presence');

        $settings['client_presence']['label']
            = trim($request->input('client_label', 'Client'));

        $settings['client_presence']['enable_addresses']
            = $request->boolean('enable_client_addresses');

        // ---- Customer capture (optional; never blocks trip start) ----
        $settings['customer']['enabled']
            = $request->boolean('enable_customer_capture');

        $settings['customer']['allow_select']
            = $request->boolean('allow_customer_select', true);

        $settings['customer']['allow_manual']
            = $request->boolean('allow_customer_manual', true);

        // ---- Safety check ----
        $settings['safety_check']['enabled']
            = $request->boolean('enable_safety_check');

        // ---- Fault / incident reporting ----
        $settings['faults']['enabled']
            = $request->boolean('enable_fault_reporting');

        $settings['faults']['allow_during_trip']
            = $request->boolean('allow_fault_during_trip', true);

        $settings['faults']['require_end_of_trip_check']
            = $request->boolean('require_end_of_trip_fault_check');

        // ---- Vehicles ----
        $settings['vehicles']['registration_tracking_enabled']
            = $request->boolean('enable_vehicle_registration_tracking');

        $settings['vehicles']['servicing_tracking_enabled']
            = $request->boolean('enable_vehicle_servicing_tracking');

        // Persist to DB
        DB::connection('sharpfleet')
            ->table('company_settings')
            ->updateOrInsert(
                ['organisation_id' => $this->organisationId],
                [
                    'organisation_id' => $this->organisationId,
                    'settings_json'   => json_encode($settings),
                ]
            );

        // Update in-memory cache
        $this->settings = $settings;
    }

    /**
     * Debug / inspection helper
     */
    public function all(): array
    {
        return $this->settings;
    }
}
