<?php

namespace App\Http\Requests\SharpFleet\Trips;

use App\Services\SharpFleet\CompanySettingsService;
use Illuminate\Foundation\Http\FormRequest;

class StartTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->session()->get('sharpfleet.user');
        $organisationId = is_array($user) && isset($user['organisation_id']) ? (int) $user['organisation_id'] : 0;

        $settingsService = $organisationId > 0 ? new CompanySettingsService($organisationId) : null;
        $settings = $settingsService ? $settingsService->all() : [];

        $manualTimesRequired = $settingsService ? $settingsService->requireManualStartEndTimes() : false;

        $tripMode = strtolower(trim((string) $this->input('trip_mode', 'business')));
        $customerEnabled = (bool) ($settings['customer']['enabled'] ?? false);
        $clientPresenceEnabled = (bool) ($settings['client_presence']['enabled'] ?? false);
        $clientAddressesEnabled = (bool) ($settings['client_presence']['enable_addresses'] ?? false);

        // If a feature is disabled, ignore any posted values (server-side enforcement).
        $customerId = $customerEnabled ? $this->input('customer_id') : null;
        $customerName = $customerEnabled ? $this->input('customer_name') : null;
        $clientPresent = $clientPresenceEnabled ? $this->input('client_present') : null;
        $clientAddress = ($clientPresenceEnabled && $clientAddressesEnabled) ? $this->input('client_address') : null;

        $startedAt = $manualTimesRequired ? $this->input('started_at') : null;

        $this->merge([
            // Ensure optional select placeholders don't fail validation.
            'customer_id' => $customerId === '' ? null : $customerId,
            'customer_name' => $customerName === '' ? null : $customerName,
            'client_present' => $clientPresent === '' ? null : $clientPresent,
            'client_address' => $clientAddress === '' ? null : $clientAddress,

            'started_at' => $startedAt === '' ? null : $startedAt,

            // Normalise legacy trip modes to 'business' so conditional rules work.
            'trip_mode' => in_array($tripMode, ['client', 'no_client', 'internal'], true) ? 'business' : $this->input('trip_mode'),
        ]);
    }

    public function rules(): array
    {
        $user = $this->session()->get('sharpfleet.user');
        $organisationId = is_array($user) && isset($user['organisation_id']) ? (int) $user['organisation_id'] : 0;

        $settingsService = $organisationId > 0 ? new CompanySettingsService($organisationId) : null;
        $settings = $settingsService ? $settingsService->all() : [];

        $odometerRequired = $settingsService ? $settingsService->odometerRequired() : true;
        $manualTimesRequired = $settingsService ? $settingsService->requireManualStartEndTimes() : false;

        $clientPresenceEnabled = (bool) ($settings['client_presence']['enabled'] ?? false);
        $clientPresenceRequired = (bool) ($settings['client_presence']['required'] ?? false);

        $safetyCheckEnabled = $settingsService ? $settingsService->safetyCheckEnabled() : false;
        $safetyItems = $settingsService ? $settingsService->safetyCheckItems() : [];
        $hasSafetyItems = is_array($safetyItems) && count($safetyItems) > 0;
        $safetyCheckRule = ($safetyCheckEnabled && $hasSafetyItems) ? ['accepted'] : ['nullable'];

        $startKmRule = $odometerRequired
            ? ['required', 'integer', 'min:0']
            : ['nullable', 'integer', 'min:0'];

        $clientPresentRule = ['nullable', 'in:0,1'];
        if ($clientPresenceEnabled && $clientPresenceRequired) {
            // Required for business trips, never for private trips.
            $clientPresentRule = ['required_unless:trip_mode,private', 'in:0,1'];
        } elseif ($clientPresenceEnabled) {
            $clientPresentRule = ['nullable', 'in:0,1'];
        }

        return [
            'vehicle_id' => ['required', 'integer'],
            'trip_mode'  => ['required', 'string'],
            'start_km'   => $startKmRule,
            'started_at' => $manualTimesRequired ? ['required', 'date'] : ['nullable', 'date'],
            'safety_check_confirmed' => $safetyCheckRule,
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'distance_method' => ['nullable', 'string'],
            'client_present' => $clientPresentRule,
            'client_address' => ['nullable', 'string'],
        ];
    }
}
