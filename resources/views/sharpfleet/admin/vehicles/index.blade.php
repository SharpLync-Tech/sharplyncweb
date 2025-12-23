@extends('layouts.base')

@section('title', 'SharpFleet Admin – Vehicles')

@section('content')
<div style="max-width:1100px;margin:60px auto;">

    <h1>Vehicles</h1>

    <p style="color:#555;">
        Vehicles registered under your organisation.
    </p>

    @if ($vehicles->isEmpty())
        <div style="background:#f9fafb;border:1px dashed #ccc;padding:30px;border-radius:8px;margin-top:30px;">
            <strong>No vehicles found.</strong>
            <p style="margin-top:10px;">
                You haven’t added any vehicles yet.
            </p>
        </div>
    @else
        <table style="width:100%;border-collapse:collapse;margin-top:30px;background:white;">
            <thead>
                <tr style="background:#f4f7fb;">
                    <th style="text-align:left;padding:12px;border-bottom:1px solid #ddd;">Name</th>
                    <th style="text-align:left;padding:12px;border-bottom:1px solid #ddd;">Registration</th>
                    <th style="text-align:left;padding:12px;border-bottom:1px solid #ddd;">Make / Model</th>
                    <th style="text-align:left;padding:12px;border-bottom:1px solid #ddd;">Type</th>
                    <th style="text-align:left;padding:12px;border-bottom:1px solid #ddd;">Active</th>
                    <th style="text-align:left;padding:12px;border-bottom:1px solid #ddd;">Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vehicles as $vehicle)
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #eee;">
                            {{ $vehicle->name }}
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee;">
                            {{ $vehicle->registration_number }}
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee;">
                            {{ $vehicle->make ?? '—' }} {{ $vehicle->model ?? '' }}
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee;">
                            {{ ucfirst($vehicle->vehicle_type) }}
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee;">
                            @if ($vehicle->is_active)
                                <span style="color:green;font-weight:bold;">Yes</span>
                            @else
                                <span style="color:#999;">No</span>
                            @endif
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee;">
                            {{ \Carbon\Carbon::parse($vehicle->updated_at)->format('d M Y') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <hr style="margin:40px 0;">

    <p>
        <a href="/app/sharpfleet/admin" style="text-decoration:none;">
            ← Back to dashboard
        </a>
    </p>

</div>
@endsection
