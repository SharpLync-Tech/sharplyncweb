@extends('layouts.sharpfleet')

@section('title', 'Company Settings')

@section('sharpfleet-content')

@php
    // Safety defaults (in case keys don’t exist yet)
    $settings = array_merge([
        'enable_client_presence'   => false,
        'require_client_presence'  => false,
        'client_label'             => 'Client',
        'require_odometer_start'   => true,
        'allow_odometer_override'  => true,
        'enable_safety_check'      => false,
        'enable_client_addresses'  => false,
    ], $settings ?? []);
@endphp

<div style="max-width:900px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Company Settings</h1>

    <p style="margin-bottom:24px;color:#6b7280;">
        These settings control how drivers use SharpFleet when starting and ending trips.
        Changes apply immediately to all drivers in your organisation.
    </p>

    {{-- Success message --}}
    @if (session('success'))
        <div style="background:#dcfce7;color:#065f46;
                    padding:12px 16px;border-radius:8px;
                    margin-bottom:24px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/settings') }}">
        @csrf

        {{-- Passenger / Client Presence --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <h2 style="margin-bottom:8px;">Passenger / Client Presence</h2>

            <p style="color:#6b7280;margin-bottom:16px;">
                Enable this if drivers need to record whether a passenger or client was present
                in the vehicle during a trip.
            </p>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="enable_client_presence" value="1"
                       {{ $settings['enable_client_presence'] ? 'checked' : '' }}>
                <strong>Enable passenger/client presence tracking</strong>
            </label>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="require_client_presence" value="1"
                       {{ $settings['require_client_presence'] ? 'checked' : '' }}>
                <strong>Block trip start unless passenger/client presence is recorded</strong>
            </label>

            <div style="margin-top:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">
                    Label shown to drivers
                </label>
                <input type="text"
                       name="client_label"
                       value="{{ $settings['client_label'] }}"
                       style="width:100%;padding:10px;">
            </div>

        </div>

        {{-- Trip Rules --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <h2 style="margin-bottom:8px;">Trip Rules</h2>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="require_odometer_start" value="1"
                       {{ $settings['require_odometer_start'] ? 'checked' : '' }}>
                <strong>Require odometer reading when starting a trip</strong>
            </label>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="allow_odometer_override" value="1"
                       {{ $settings['allow_odometer_override'] ? 'checked' : '' }}>
                <strong>Allow drivers to override the auto-filled odometer</strong>
            </label>

        </div>

        {{-- Client Address Tracking --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <h2 style="margin-bottom:8px;">Client Address Tracking</h2>

            <p style="color:#6b7280;margin-bottom:16px;">
                Enable this if your business needs to record client addresses for billing or job tracking (e.g., tradies).
                Disabled by default for privacy.
            </p>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="enable_client_addresses" value="1"
                       {{ $settings['enable_client_addresses'] ? 'checked' : '' }}>
                <strong>Allow recording client addresses</strong>
            </label>
        </div>

        {{-- Safety Check --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:32px;">

            <h2 style="margin-bottom:8px;">Pre-Drive Safety Check</h2>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="enable_safety_check" value="1"
                       {{ $settings['enable_safety_check'] ? 'checked' : '' }}>
                <strong>Enable safety check before trips</strong>
            </label>

            <p style="margin-left:24px;color:#6b7280;font-style:italic;">
                No safety checklist has been configured yet.
            </p>

        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit"
                    name="save"
                    value="1"
                    style="background:#2CBFAE;color:white;
                           border:none;padding:12px 20px;
                           border-radius:6px;font-weight:600;">
                Save settings
            </button>

            <button type="submit"
                    name="save_and_return"
                    value="1"
                    style="background:#e5e7eb;color:#111827;
                           border:none;padding:12px 20px;
                           border-radius:6px;font-weight:600;">
                Save & return to Company
            </button>
        </div>

        <div style="margin-top:40px;font-size:14px;color:#6b7280;">
            These settings apply to all drivers in your organisation and take effect immediately.
        </div>

    </form>
</div>

@endsection
