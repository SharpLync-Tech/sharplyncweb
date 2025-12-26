@extends('layouts.sharpfleet')

@section('title', 'Trip Reports')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Trip Reports</h1>
                <p class="page-description">View and export trip data for reporting.</p>
            </div>
            <div class="btn-group">
                <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}" class="d-inline">
                    <input type="hidden" name="export" value="csv">
                    <button type="submit" class="btn btn-primary">Export CSV</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <div class="grid grid-3">
                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }} ({{ $v->registration_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary mt-3">Filter</button>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card">
        <div class="card-body">
            @if($trips->count() === 0)
                <p class="text-muted fst-italic">No trips found matching the filters.</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Trip Mode</th>
                                <th>Start KM</th>
                                <th>End KM</th>
                                <th>Client Present</th>
                                <th>Client Address</th>
                                <th>Started At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $t->vehicle_name }}<br>
                                        <small class="text-muted">{{ $t->registration_number }}</small>
                                    </td>
                                    <td>{{ $t->driver_name }}</td>
                                    <td>{{ ucfirst($t->trip_mode) }}</td>
                                    <td>{{ number_format($t->start_km) }}</td>
                                    <td>{{ $t->end_km ? number_format($t->end_km) : '—' }}</td>
                                    <td>{{ $t->client_present ? 'Yes' : 'No' }}</td>
                                    <td>{{ $t->client_address ?: '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($t->started_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection