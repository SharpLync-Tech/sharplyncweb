@extends('layouts.sharpfleet')

@section('title', 'Vehicles')

@section('sharpfleet-content')

<div style="max-width:1000px;margin:40px auto;padding:0 16px;">

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <h1 style="margin:0 0 6px 0;">Vehicles</h1>
            <p style="margin:0;color:#6b7280;">Manage vehicles for your organisation.</p>
        </div>

        <a href="{{ url('/app/sharpfleet/admin/vehicles/create') }}"
           style="background:#2CBFAE;color:white;padding:12px 16px;border-radius:6px;text-decoration:none;font-weight:600;">
            + Add Vehicle
        </a>
    </div>

    @if (session('success'))
        <div style="background:#dcfce7;color:#065f46;padding:12px 16px;border-radius:8px;margin:20px 0;">
            {{ session('success') }}
        </div>
    @endif

    <div style="background:white;padding:20px;border-radius:10px;
                box-shadow:0 4px 12px rgba(0,0,0,0.05);
                margin-top:20px;">

        @if($vehicles->count() === 0)
            <p style="color:#9ca3af;font-style:italic;margin:0;">
                No vehicles found.
            </p>
        @else
            <div style="overflow:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left;border-bottom:1px solid #e5e7eb;">
                            <th style="padding:10px 8px;">Name</th>
                            <th style="padding:10px 8px;">Rego</th>
                            <th style="padding:10px 8px;">Type</th>
                            <th style="padding:10px 8px;">Class</th>
                            <th style="padding:10px 8px;">Make/Model</th>
                            <th style="padding:10px 8px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $v)
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:10px 8px;font-weight:600;">{{ $v->name }}</td>
                                <td style="padding:10px 8px;">{{ $v->registration_number }}</td>
                                <td style="padding:10px 8px;">{{ ucfirst($v->vehicle_type) }}</td>
                                <td style="padding:10px 8px;">{{ $v->vehicle_class ?? '—' }}</td>
                                <td style="padding:10px 8px;">
                                    {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '—' }}
                                </td>
                                <td style="padding:10px 8px;">
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <a href="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/edit') }}"
                                           style="background:#e5e7eb;color:#111827;padding:8px 10px;border-radius:6px;text-decoration:none;font-weight:600;">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/archive') }}"
                                              onsubmit="return confirm('Archive this vehicle? Drivers will no longer be able to select it.');">
                                            @csrf
                                            <button type="submit"
                                                style="background:#fee2e2;color:#7f1d1d;border:none;padding:8px 10px;border-radius:6px;font-weight:600;cursor:pointer;">
                                                Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

</div>

@endsection
