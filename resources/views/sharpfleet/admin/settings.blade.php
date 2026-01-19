@extends('layouts.sharpfleet')

@section('title', 'Company Settings')

@section('sharpfleet-content')
<style>
    .sf-tooltip { position: relative; display: inline-flex; align-items: center; }
    .sf-tooltip__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        margin-left: 6px;
        border-radius: 50%;
        border: 1px solid #1ba5a5;
        color: #1ba5a5;
        font-size: 12px;
        line-height: 1;
        cursor: help;
        background: #f3fbfb;
    }
    .sf-tooltip__content {
        position: absolute;
        left: 0;
        top: calc(100% + 8px);
        min-width: 280px;
        max-width: 380px;
        padding: 12px 14px;
        border-radius: 10px;
        background: #0f2f2f;
        color: #e9f7f7;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        font-size: 13px;
        line-height: 1.4;
        z-index: 20;
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity 120ms ease, transform 120ms ease;
    }
    .sf-tooltip__content strong { display: block; margin-bottom: 6px; color: #bff3f3; }
    .sf-tooltip__content span { display: block; margin-top: 6px; }
    .sf-tooltip:hover .sf-tooltip__content,
    .sf-tooltip:focus-within .sf-tooltip__content {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
</style>

@php
    // Safety defaults (nested shape – matches CompanySettingsService::all())
    $settings = array_replace_recursive([
        'trip' => [
            'odometer_required'       => true,
            'odometer_allow_override' => true,
            'allow_private_trips'     => false,
            'require_manual_start_end_times' => false,
        ],
        'client_presence' => [
            'enabled'         => false,
            'required'        => false,
            'label'           => 'Client',
            'enable_addresses'=> false,
        ],
        'customer' => [
            'enabled'      => false,
            'allow_select' => true,
            'allow_manual' => true,
        ],
        'safety_check' => [
            'enabled' => false,
        ],

        'vehicles' => [
            'registration_tracking_enabled' => true,
            'servicing_tracking_enabled'    => false,
        ],

        'reminders' => [
            'registration_days' => 30,
            'service_days' => 30,
            'service_reading_threshold' => 500,
        ],
    ], $settings ?? []);

@endphp

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Company Settings</h1>
        <p class="page-description">
            These settings control how drivers use SharpFleet when starting and ending trips.
            Changes apply immediately to all drivers in your organisation.
        </p>
    </div>

    {{-- Success message --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/settings') }}">
        @csrf

        {{-- Passenger / Client Presence --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Passenger / Client Presence</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Enable this if drivers need to record whether a passenger or client was present
                    in the vehicle during a trip.
                </p>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_client_presence" value="1"
                               {{ $settings['client_presence']['enabled'] ? 'checked' : '' }}>
                        <strong>Enable passenger/client presence tracking</strong>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="require_client_presence" value="1"
                               {{ $settings['client_presence']['required'] ? 'checked' : '' }}>
                        <strong>Block trip start unless passenger/client presence is recorded</strong>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Label shown to drivers</label>
                    <input type="text" name="client_label" value="{{ $settings['client_presence']['label'] }}" class="form-control">
                </div>

                <div class="mt-4">
                    <h3 class="card-title">Customer / Client</h3>
                    <p class="text-muted mb-3">
                        Optional customer capture. Drivers can select a customer from your list or type a new name.
                        This will never block a trip from starting.
                    </p>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="enable_customer_capture" value="1"
                                   {{ $settings['customer']['enabled'] ? 'checked' : '' }}>
                            <strong>Enable customer selection/entry on client trips</strong>
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="allow_customer_select" value="1"
                                   {{ $settings['customer']['allow_select'] ? 'checked' : '' }}>
                            <strong>Allow selecting from admin customer list</strong>
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="allow_customer_manual" value="1"
                                   {{ $settings['customer']['allow_manual'] ? 'checked' : '' }}>
                            <strong>Allow manual customer name entry (not in list)</strong>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Trip Rules --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Trip Rules</h2>
            </div>
            <div class="card-body">
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="require_odometer_start" value="1"
                               {{ $settings['trip']['odometer_required'] ? 'checked' : '' }}>
                        <strong>Require starting reading when starting a trip (km or hours)</strong>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_odometer_override" value="1"
                               {{ $settings['trip']['odometer_allow_override'] ? 'checked' : '' }}>
                        <strong>Allow drivers to override the auto-filled reading (km or hours)</strong>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_private_trips" value="1"
                               {{ filter_var(($settings['trip']['allow_private_trips'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                        <strong>Allow private use of fleet vehicles</strong>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_private_vehicle_slots" value="1"
                               {{ filter_var(($settings['trip']['private_vehicle_slots_enabled'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                        <strong>Allow private vehicle use when fleet vehicles are unavailable</strong>
                        <span class="sf-tooltip" aria-label="What does this setting do?">
                            <span class="sf-tooltip__icon">ⓘ</span>
                            <span class="sf-tooltip__content" role="tooltip">
                                <strong>What does this setting do?</strong>
                                <span>Enable this setting to allow drivers to record trips using their own private vehicles when no fleet vehicles are available due to active trips, servicing, or repairs.</span>
                                <span>This ensures all trips are still logged correctly against jobs, customers, and drivers, even when a fleet vehicle cannot be used.</span>
                                <span>Private vehicle use is intended for occasional, real-world situations and is limited to prevent it from replacing fleet vehicles. Once the allowed number of private vehicle uses is reached, additional private vehicle trips cannot be started until an existing trip is completed.</span>
                            </span>
                        </span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="require_manual_start_end_times" value="1"
                               {{ filter_var(($settings['trip']['require_manual_start_end_times'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                        <strong>Require drivers to enter a start time and end time for each trip</strong>
                    </label>
                    
                        <label class="checkbox-label">
                        <input type="checkbox" name="enable_purpose_of_travel" value="1"
                               {{ filter_var(($settings['trip']['purpose_of_travel_enabled'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                            <strong>Enable Purpose of Travel (business trips)</strong>
                        </label>
                </div>
            </div>
        </div>

        {{-- Vehicle Tracking --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Vehicle Tracking</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    These settings control which extra admin-managed details can be captured per vehicle.
                </p>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_vehicle_registration_tracking" value="1"
                               {{ $settings['vehicles']['registration_tracking_enabled'] ? 'checked' : '' }}>
                        <strong>Enable Vehicle Registration Tracking</strong>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_vehicle_servicing_tracking" value="1"
                               {{ $settings['vehicles']['servicing_tracking_enabled'] ? 'checked' : '' }}>
                        <strong>Enable Vehicle Servicing Tracking</strong>
                    </label>
                </div>
            </div>
        </div>

        {{-- Reminder Emails --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Reminder Emails</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    These settings control when SharpFleet sends registration and servicing reminder emails.
                    They apply at a company level (not per vehicle).
                </p>

                <div class="form-group">
                    <label class="form-label">Rego window (days)</label>
                    <input type="number" min="1" step="1" name="reminder_registration_days"
                           value="{{ (int) ($settings['reminders']['registration_days'] ?? 30) }}" class="form-control">
                    <div class="text-muted small mt-1">Vehicles with rego expiring within this window will be marked as “Due soon”.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Service (date) window (days)</label>
                    <input type="number" min="1" step="1" name="reminder_service_days"
                           value="{{ (int) ($settings['reminders']['service_days'] ?? 30) }}" class="form-control">
                    <div class="text-muted small mt-1">Vehicles with service due dates within this window will be marked as “Due soon”.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Service (reading) threshold</label>
                    <input type="number" min="0" step="1" name="reminder_service_reading_threshold"
                           value="{{ (int) ($settings['reminders']['service_reading_threshold'] ?? 500) }}" class="form-control">
                    <div class="text-muted small mt-1">If a vehicle is within this many km/hours of its due reading, it will be marked as “Due soon”.</div>
                </div>
            </div>
        </div>

        {{-- Client Address Tracking --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Client Address Tracking</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Enable this if your business needs to record client addresses for billing or job tracking (e.g., tradies).
                    Disabled by default for privacy.
                </p>

                <label class="checkbox-label">
                    <input type="checkbox" name="enable_client_addresses" value="1"
                           {{ $settings['client_presence']['enable_addresses'] ? 'checked' : '' }}>
                    <strong>Allow recording client addresses</strong>
                </label>
            </div>
        </div>

        {{-- Safety Check --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Pre-Drive Safety Check</h2>
            </div>
            <div class="card-body">
                <label class="checkbox-label">
                    <input type="checkbox" name="enable_safety_check" value="1"
                           {{ $settings['safety_check']['enabled'] ? 'checked' : '' }}>
                    <strong>Enable safety check before trips</strong>
                </label>

                @php
                    $safetyItems = $settings['safety_check']['items'] ?? [];
                    $safetyCount = is_array($safetyItems) ? count($safetyItems) : 0;
                @endphp

                <p class="text-muted ms-4">
                    @if($safetyCount > 0)
                        Checklist items configured: <strong>{{ $safetyCount }}</strong>.
                        <a href="{{ url('/app/sharpfleet/admin/safety-checks') }}">Edit checklist</a>
                    @else
                        No safety checklist has been configured yet.
                        <a href="{{ url('/app/sharpfleet/admin/safety-checks') }}">Configure checklist</a>
                    @endif
                </p>
            </div>
        </div>

        {{-- Vehicle Issue / Accident Reporting --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Vehicle Issue / Accident Reporting</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Enable this if drivers need to report vehicle issues or accidents against a vehicle.
                    When enabled, drivers will see a “Report a Vehicle Issue / Accident” option in their portal.
                </p>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_fault_reporting" value="1"
                               {{ ($settings['faults']['enabled'] ?? false) ? 'checked' : '' }}>
                           <strong>Enable vehicle issue/accident reporting</strong>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_fault_during_trip" value="1"
                               {{ ($settings['faults']['allow_during_trip'] ?? true) ? 'checked' : '' }}>
                           <strong>Allow drivers to report issues/accidents during a trip</strong>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="require_end_of_trip_fault_check" value="1"
                               {{ ($settings['faults']['require_end_of_trip_check'] ?? false) ? 'checked' : '' }}>
                        <strong>Require a quick issue/accident check when ending a trip (coming soon)</strong>
                    </label>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="btn-group">
            <button type="submit" name="save" value="1" class="btn btn-primary">Save settings</button>
            <button type="submit" name="save_and_return" value="1" class="btn btn-secondary">Save & return to Company</button>
        </div>

        <div class="mt-5 text-muted small">
            These settings apply to all drivers in your organisation and take effect immediately.
        </div>

    </form>
</div>

@endsection
