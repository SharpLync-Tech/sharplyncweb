@extends('layouts.sharpfleet')

@section('title', 'Company Settings')

@section('sharpfleet-content')

<div style="max-width:900px;margin:40px auto;padding:0 16px;">

    {{-- Page header --}}
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

    <form method="POST" action="{{ url('/app/sharpfleet/admin/company-settings') }}">
        @csrf

        {{-- ===============================
             Passenger / Client Presence
        =============================== --}}
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
                       {{ $settings->enable_client_presence ? 'checked' : '' }}>
                <strong>Enable passenger/client presence tracking</strong>
            </label>

            <p style="margin-left:24px;margin-bottom:16px;color:#6b7280;">
                Drivers will see a prompt when starting a trip.
            </p>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="require_client_presence" value="1"
                       {{ $settings->require_client_presence ? 'checked' : '' }}>
                <strong>Block trip start unless passenger/client presence is recorded</strong>
            </label>

            <p style="margin-left:24px;color:#6b7280;">
                Trips cannot start until this information is provided.
            </p>

            <div style="margin-top:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">
                    Label shown to drivers
                </label>
                <input type="text"
                       name="client_label"
                       value="{{ $settings->client_label }}"
                       style="width:100%;padding:10px;"
                       placeholder="e.g. Client, Passenger, Participant">

                <p style="color:#6b7280;margin-top:6px;">
                    Customise the wording used in the driver app.
                </p>
            </div>

        </div>

        {{-- ===============================
             Trip Rules
        =============================== --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <h2 style="margin-bottom:8px;">Trip Rules</h2>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="require_odometer_start" value="1"
                       {{ $settings->require_odometer_start ? 'checked' : '' }}>
                <strong>Require odometer reading when starting a trip</strong>
            </label>

            <p style="margin-left:24px;margin-bottom:16px;color:#6b7280;">
                Drivers must enter a starting odometer reading before a trip can begin.
            </p>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="allow_odometer_override" value="1"
                       {{ $settings->allow_odometer_override ? 'checked' : '' }}>
                <strong>Allow drivers to override the auto-filled odometer</strong>
            </label>

            <p style="margin-left:24px;color:#6b7280;">
                Useful if the vehicle’s recorded odometer is incorrect.
            </p>

        </div>

        {{-- ===============================
             Safety Check
        =============================== --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:32px;">

            <h2 style="margin-bottom:8px;">Pre-Drive Safety Check</h2>

            <p style="color:#6b7280;margin-bottom:16px;">
                Require drivers to complete a quick safety check before starting a trip.
            </p>

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" name="enable_safety_check" value="1"
                       {{ $settings->enable_safety_check ? 'checked' : '' }}>
                <strong>Enable safety check before trips</strong>
            </label>

            <p style="margin-left:24px;color:#6b7280;font-style:italic;">
                No safety checklist has been configured yet.
            </p>

        </div>

        {{-- ===============================
             Actions
        =============================== --}}
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

        {{-- Footer note --}}
        <div style="margin-top:40px;font-size:14px;color:#6b7280;">
            These settings apply to all drivers in your organisation and take effect immediately.
        </div>

    </form>

</div>

@endsection
