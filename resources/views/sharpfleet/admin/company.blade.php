@extends('layouts.sharpfleet')

@section('title', 'Company')

@section('sharpfleet-content')

<div style="max-width:900px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Company</h1>

    <p style="margin-bottom:24px;color:#6b7280;">
        Overview of your organisation’s configuration in SharpFleet.
    </p>

    <div style="background:white;padding:20px;border-radius:10px;
                box-shadow:0 4px 12px rgba(0,0,0,0.05);
                margin-bottom:24px;">

        <h2 style="margin-bottom:16px;">Company Details</h2>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <div style="font-size:12px;color:#6b7280;">Company name</div>
                <div style="font-weight:600;">{{ $companyName }}</div>
            </div>

            <div>
                <div style="font-size:12px;color:#6b7280;">Type</div>
                <div style="font-weight:600;">{{ $companyType }}</div>
            </div>

            <div>
                <div style="font-size:12px;color:#6b7280;">Industry</div>
                <div style="font-weight:600;">{{ $industry }}</div>
            </div>

            <div>
                <div style="font-size:12px;color:#6b7280;">Timezone</div>
                <div style="font-weight:600;">{{ $timezone }}</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
        <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <h3>Drivers</h3>
            <div style="font-size:32px;font-weight:700;">{{ $driversCount }}</div>
        </div>

        <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <h3>Vehicles</h3>
            <div style="font-size:32px;font-weight:700;">{{ $vehiclesCount }}</div>
        </div>
    </div>

    <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom:16px;">Actions</h2>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="{{ url('/app/sharpfleet/admin/company/profile') }}"
               style="background:#2CBFAE;color:white;padding:12px 16px;border-radius:6px;text-decoration:none;font-weight:600;">
                Edit Company Details
            </a>

            <a href="{{ url('/app/sharpfleet/admin/settings') }}"
               style="background:#e5e7eb;color:#111827;padding:12px 16px;border-radius:6px;text-decoration:none;font-weight:600;">
                Company Settings
            </a>
        </div>
    </div>

</div>

@endsection
