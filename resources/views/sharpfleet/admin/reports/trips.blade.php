@extends('layouts.sharpfleet')

@section('title', 'Trip Reports')

@section('sharpfleet-content')

@php
    use App\Services\SharpFleet\CompanySettingsService;

    $user = session('sharpfleet.user');
    $settingsService = new CompanySettingsService((int) $user['organisation_id']);
    $companyTimezone = $settingsService->timezone();
@endphp

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
                    <input type="hidden" name="vehicle_id" value="{{ request('vehicle_id') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
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

                <div class="grid grid-3 mt-3">
                    <div class="form-group">
                        <label class="form-label">Customer</label>
                        @if(!empty($hasCustomersTable) && $hasCustomersTable)
                            <select name="customer_id" class="form-control">
                                <option value="">All Customers</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <select class="form-control" disabled>
                                <option>Customers table not available</option>
                            </select>
                        @endif
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
                <div class="mb-3">
                    <span class="text-muted">Total:</span>
                    <span class="fw-bold">{{ number_format($totals['km'] ?? 0, 2) }} km</span>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">{{ number_format($totals['hours'] ?? 0, 2) }} hours</span>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Trip Mode</th>
                                <th>Customer</th>
                                <th>Unit</th>
                                <th>Start Reading</th>
                                <th>End Reading</th>
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
                                    @php
                                        $rawMode = strtolower((string) ($t->trip_mode ?? ''));
                                        $modeLabel = $rawMode === 'private' ? 'Private' : 'Business';
                                    @endphp
                                    <td>{{ $modeLabel }}</td>
                                    <td>{{ $t->customer_name_display ?: '—' }}</td>
                                    <td>{{ ($t->tracking_mode ?? 'distance') === 'hours' ? 'hours' : 'km' }}</td>
                                    <td>{{ number_format($t->start_km) }}</td>
                                    <td>{{ $t->end_km ? number_format($t->end_km) : '—' }}</td>
                                    <td>{{ $t->client_present ? 'Yes' : 'No' }}</td>
                                    <td>{{ $t->client_address ?: '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($t->started_at)->timezone($companyTimezone)->format('d/m/Y H:i') }}</td>
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