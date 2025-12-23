<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Facades\DB;

class CompanySettingsService
{
    /**
     * Default settings (used if DB row or keys are missing)
     */
    protected array $defaults = [
        'timezone'    => 'Australia/Brisbane',
        'date_format' => 'd/m/Y',
        'time_format' => 'H:i',

        'trip' => [
            'odometer_required'                  => true,
            'odometer_autofill_from_last_trip'   => true,
            'odometer_allow_override'            => true,
            'trip_type_enabled'                  => true,
            'trip_type_required'                 => true,
            'allow_private_trips'                => true,
        ],

        'client_presence' => [
            'enabled'                     => false,
            'required'                    => false,
            'label'                       => 'Client',
            'require_details_when_present'=> false,
        ],

        'safety_check' => [
            'enabled'  => false,
            'required' => false,
            'items'    => [],
        ],

        'faults' => [
            'allow_during_trip'        => true,
            'require_end_of_trip_check'=> false,
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
        $this->settings = $this->loadSettings($organisationId);
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
        return (bool) $this->settings['trip']['odometer_autofill_from_last_trip'];
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
        return (bool) $this->settings['trip']['allow_private_trips'];
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

    public function requireEndOfTripFaultCheck(): bool
    {
        return (bool) $this->settings['faults']['require_end_of_trip_check'];
    }

    /**
     * Debug / inspection helper
     */
    public function all(): array
    {
        return $this->settings;
    }
}
