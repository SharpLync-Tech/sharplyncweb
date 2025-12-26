@extends('layouts.sharpfleet')

@section('title', 'Trip Reports')

@section('sharpfleet-content')

<div style="max-width:1200px;margin:40px auto;padding:0 16px;">

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <h1 style="margin:0 0 6px 0;">Trip Reports</h1>
            <p style="margin:0;color:#6b7280;">View and export trip data for reporting.</p>
        </div>

        <div style="display:flex;gap:8px;">
            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}" style="display:inline;">
                <input type="hidden" name="export" value="csv">
                <button type="submit" style="background:#2CBFAE;color:white;padding:12px 16px;border-radius:6px;text-decoration:none;font-weight:600;border:none;cursor:pointer;">
                    Export CSV
                </button>
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);margin:20px 0;">
        <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Vehicle</label>
                    <select name="vehicle_id" style="width:100%;padding:10px;">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }} ({{ $v->registration_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" style="width:100%;padding:10px;">
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" style="width:100%;padding:10px;">
                </div>
            </div>
            <button type="submit" style="margin-top:16px;background:#e5e7eb;color:#111827;padding:10px 16px;border-radius:6px;border:none;font-weight:600;cursor:pointer;">
                Filter
            </button>
        </form>
    </div>

    {{-- Results --}}
    <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        @if($trips->count() === 0)
            <p style="color:#9ca3af;font-style:italic;margin:0;">No trips found matching the filters.</p>
        @else
            <div style="overflow:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left;border-bottom:1px solid #e5e7eb;">
                            <th style="padding:10px 8px;">Vehicle</th>
                            <th style="padding:10px 8px;">Driver</th>
                            <th style="padding:10px 8px;">Trip Mode</th>
                            <th style="padding:10px 8px;">Start KM</th>
                            <th style="padding:10px 8px;">End KM</th>
                            <th style="padding:10px 8px;">Client Present</th>
                            <th style="padding:10px 8px;">Client Address</th>
                            <th style="padding:10px 8px;">Started At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trips as $t)
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:10px 8px;font-weight:600;">{{ $t->vehicle_name }}<br><small style="color:#6b7280;">{{ $t->registration_number }}</small></td>
                                <td style="padding:10px 8px;">{{ $t->driver_name }}</td>
                                <td style="padding:10px 8px;">{{ ucfirst($t->trip_mode) }}</td>
                                <td style="padding:10px 8px;">{{ number_format($t->start_km) }}</td>
                                <td style="padding:10px 8px;">{{ $t->end_km ? number_format($t->end_km) : '—' }}</td>
                                <td style="padding:10px 8px;">{{ $t->client_present ? 'Yes' : 'No' }}</td>
                                <td style="padding:10px 8px;">{{ $t->client_address ?: '—' }}</td>
                                <td style="padding:10px 8px;">{{ \Carbon\Carbon::parse($t->started_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@endsection