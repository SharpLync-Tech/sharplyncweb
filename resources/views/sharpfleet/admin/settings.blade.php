@extends('layouts.sharpfleet')

@section('title', 'Company Settings')

@section('sharpfleet-content')

@php
    // Safety defaults (nested shape – matches CompanySettingsService::all())
    $settings = array_replace_recursive([
        'trip' => [
            'odometer_required'       => true,
            'odometer_allow_override' => true,
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

                <p class="text-muted ms-4 fst-italic">
                    No safety checklist has been configured yet.
                </p>
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
