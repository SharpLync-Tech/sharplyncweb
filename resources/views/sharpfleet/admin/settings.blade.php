@extends('layouts.sharpfleet')

@section('title', 'Company Settings')

@section('sharpfleet-content')
<div style="max-width:900px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Company Settings</h1>
    <p style="color:#555;margin-bottom:24px;">
        Configure how SharpFleet behaves for your company.
    </p>

    {{-- Success message --}}
    @if (session('success'))
        <div style="background:#d1fae5;color:#065f46;
                    padding:12px;border-radius:8px;margin-bottom:24px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/app/sharpfleet/admin/settings">
        @csrf

        {{-- ================================
             Client Presence
        ================================= --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <h2 style="margin-bottom:12px;">Client Presence</h2>
            <p style="color:#555;margin-bottom:16px;">
                Enable this if drivers must record whether a client was present in the vehicle.
            </p>

            <label style="display:block;margin-bottom:10px;">
                <input type="checkbox"
                       name="client_presence_enabled"
                       value="1"
                       {{ ($settings['client_presence']['enabled'] ?? false) ? 'checked' : '' }}>
                Enable client presence tracking
            </label>

            <label style="display:block;margin-bottom:10px;">
                <input type="checkbox"
                       name="client_presence_required"
                       value="1"
                       {{ ($settings['client_presence']['required'] ?? false) ? 'checked' : '' }}>
                Require client presence to be recorded on trips
            </label>

            <div style="margin-top:12px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">
                    Label used in the app
                </label>
                <input type="text"
                       name="client_presence_label"
                       value="{{ $settings['client_presence']['label'] ?? 'Client' }}"
                       style="width:100%;padding:10px;"
                       placeholder="Client / Passenger / Patient">
            </div>
        </div>

        {{-- ================================
             Trip Rules
        ================================= --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <h2 style="margin-bottom:12px;">Trip Rules</h2>

            <label style="display:block;margin-bottom:10px;">
                <input type="checkbox"
                       name="odometer_required"
                       value="1"
                       {{ ($settings['trip']['odometer_required'] ?? true) ? 'checked' : '' }}>
                Odometer is required when starting a trip
            </label>

            <label style="display:block;">
                <input type="checkbox"
                       name="odometer_allow_override"
                       value="1"
                       {{ ($settings['trip']['odometer_allow_override'] ?? true) ? 'checked' : '' }}>
                Allow drivers to override auto-filled odometer
            </label>
        </div>

        {{-- ================================
             Safety Check
        ================================= --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <h2 style="margin-bottom:12px;">Safety Check</h2>
            <p style="color:#555;margin-bottom:16px;">
                Optional pre-drive safety checklist before starting a trip.
            </p>

            <label style="display:block;">
                <input type="checkbox"
                       name="safety_check_enabled"
                       value="1"
                       {{ ($settings['safety_check']['enabled'] ?? false) ? 'checked' : '' }}>
                Enable safety check before trips
            </label>
        </div>

        {{-- ================================
             Save
        ================================= --}}
        <div style="display:flex;gap:12px;">
            <button type="submit"
                    style="background:#2CBFAE;color:white;
                           border:none;padding:12px 20px;
                           font-weight:600;border-radius:6px;">
                Save Settings
            </button>

            <a href="/app/sharpfleet/admin"
               style="align-self:center;color:#555;text-decoration:none;">
                Cancel
            </a>
        </div>

    </form>
</div>
@endsection
