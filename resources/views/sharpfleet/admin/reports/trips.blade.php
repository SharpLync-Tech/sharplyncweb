@extends('layouts.sharpfleet')

@section('title', 'Trip Reports')

@section('sharpfleet-content')

@php
    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $uiVehicleId = $ui['vehicle_id'] ?? request('vehicle_id');
    $uiStartDate = $ui['start_date'] ?? request('start_date');
    $uiEndDate = $ui['end_date'] ?? request('end_date');
    $uiCustomerId = $ui['customer_id'] ?? request('customer_id');
    $uiBranchIds = $ui['branch_ids'] ?? request('branch_ids', []);
    $uiBranchIds = is_array($uiBranchIds) ? $uiBranchIds : [$uiBranchIds];
    $showBranchFilter = (bool) ($ui['show_branch_filter'] ?? false);
    $filtersGridClass = $showBranchFilter ? 'grid grid-4' : 'grid grid-3';
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
                    <input type="hidden" name="vehicle_id" value="{{ $uiVehicleId }}">
                    <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                    <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                    <input type="hidden" name="customer_id" value="{{ $uiCustomerId }}">
                    @foreach($uiBranchIds as $bid)
                        @if(is_numeric($bid) && (int) $bid > 0)
                            <input type="hidden" name="branch_ids[]" value="{{ (int) $bid }}">
                        @endif
                    @endforeach
                    <button type="submit" class="btn btn-primary">Export CSV</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <div class="alert alert-info mb-3">
                    <strong>Applied settings</strong><br>
                    Reporting period: {{ $applied['date_range_label'] ?? '—' }}<br>
                    Private trips included: {{ ($applied['include_private_trips'] ?? false) ? 'Yes' : 'No' }}<br>
                    @if(($applied['branch_filter_enabled'] ?? false))
                        Branches: {{ $applied['branch_label'] ?? 'All branches' }}<br>
                    @endif
                    Vehicle filter: {{ $applied['vehicle_label'] ?? 'All vehicles' }}<br>
                    Customer linking: {{ ($applied['customer_linking_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}<br>
                    Customer filter: {{ $applied['customer_label'] ?? 'All customers' }}
                </div>

                <div class="{{ $filtersGridClass }}">
                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (string)$uiVehicleId === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" value="{{ $uiStartDate }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" value="{{ $uiEndDate }}" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary mt-3">Filter</button>

                <div class="mt-2 text-muted">
                    Times shown in {{ $companyTimezone }}
                </div>
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
                                <th>Customer</th>
                                @if(($purposeOfTravelEnabled ?? false))
                                    <th>Purpose of Travel</th>
                                @endif
                                <th>Started At</th>
                                <th>Ended At</th>
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
                                    <td>{{ strtolower((string)$t->trip_mode) === 'private' ? 'Private' : 'Business' }}</td>

                                    {{-- CUSTOMER --}}
                                    <td>
                                        {{ $t->customer_name_display ?: '—' }}

                                        @if(empty($t->customer_id) && !empty($t->customer_name_display))
                                            <div class="mt-1">
                                                <a
                                                    class="text-primary small"
                                                    href="{{ url('/app/sharpfleet/admin/customers/create') . '?' . http_build_query([
                                                        'name'    => $t->customer_name_display,
                                                        'trip_id' => $t->id,
                                                        'return'  => 'trips',
                                                    ]) }}"
                                                >
                                                    Convert to customer
                                                </a>

                                            </div>
                                        @endif
                                    </td>

                                    @if(($purposeOfTravelEnabled ?? false))
                                        <td>{{ $t->purpose_of_travel ?: '—' }}</td>
                                    @endif

                                    <td>{{ \Carbon\Carbon::parse($t->started_at)->timezone($companyTimezone)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{ $t->end_time
                                            ? \Carbon\Carbon::parse($t->end_time)->timezone($companyTimezone)->format('d/m/Y H:i')
                                            : '—'
                                        }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-2 text-muted small">
                    Distances are shown using each branch’s local measurement unit.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
