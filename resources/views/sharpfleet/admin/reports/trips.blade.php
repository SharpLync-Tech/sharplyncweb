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
                    @if(!empty($uiBranchIds))
                        @foreach($uiBranchIds as $bid)
                            @if(is_numeric($bid) && (int) $bid > 0)
                                <input type="hidden" name="branch_ids[]" value="{{ (int) $bid }}">
                            @endif
                        @endforeach
                    @endif
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
                    @if(!empty($applied['override_note']))
                        <br>{{ $applied['override_note'] }}
                    @endif
                </div>

                <div class="{{ $filtersGridClass }}">
                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control" {{ !($ui['allow_vehicle_override'] ?? true) ? 'disabled' : '' }}>
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (string)$uiVehicleId === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                                </option>
                            @endforeach
                        </select>
                        @if(!($ui['allow_vehicle_override'] ?? true))
                            <div class="text-muted mt-1">Vehicle selection is locked by company settings.</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" value="{{ $uiStartDate }}" class="form-control" {{ !($ui['allow_date_override'] ?? true) ? 'disabled' : '' }}>
                        @if(!($ui['allow_date_override'] ?? true))
                            <div class="text-muted mt-1">Date range is locked by company settings.</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" value="{{ $uiEndDate }}" class="form-control" {{ !($ui['allow_date_override'] ?? true) ? 'disabled' : '' }}>
                    </div>

                    @if($showBranchFilter)
                        <div class="form-group">
                            <label class="form-label">Branches</label>
                            <select name="branch_ids[]" class="form-control" multiple {{ !($ui['allow_branch_override'] ?? true) ? 'disabled' : '' }}>
                                <option value="all" {{ empty(array_filter($uiBranchIds, fn ($v) => is_numeric($v) && (int) $v > 0)) ? 'selected' : '' }}>All branches</option>
                                @foreach(($branches ?? collect()) as $branch)
                                    <option value="{{ $branch->id }}" {{ in_array((string) $branch->id, array_map('strval', $uiBranchIds), true) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(!($ui['allow_branch_override'] ?? true))
                                <div class="text-muted mt-1">Branch selection is locked by company settings.</div>
                            @endif
                        </div>
                    @endif
                </div>

                @if(($ui['show_customer_filter'] ?? false) && ($hasCustomersTable ?? false))
                    <div class="grid grid-3 mt-3">
                        <div class="form-group">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-control" {{ !($ui['allow_customer_override'] ?? true) ? 'disabled' : '' }}>
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (string)$uiCustomerId === (string)$customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(!($ui['allow_customer_override'] ?? true))
                                <div class="text-muted mt-1">Customer selection is locked by company settings.</div>
                            @endif
                        </div>
                    </div>
                @endif

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
                <div class="mb-3">
                    <span class="text-muted">Total:</span>
                    @php
                        $totalKm = (float) ($totals['distance_km'] ?? 0);
                        $totalMi = (float) ($totals['distance_mi'] ?? 0);
                        $hasKm = $totalKm > 0;
                        $hasMi = $totalMi > 0;
                    @endphp
                    @if($hasKm && $hasMi)
                        <span class="fw-bold">{{ number_format($totalKm, 2) }} km</span>
                        <span class="text-muted">(km branches)</span>
                        <span class="text-muted">/</span>
                        <span class="fw-bold">{{ number_format($totalMi, 2) }} mi</span>
                        <span class="text-muted">(mi branches)</span>
                    @elseif($hasMi)
                        <span class="fw-bold">{{ number_format($totalMi, 2) }} mi</span>
                    @else
                        <span class="fw-bold">{{ number_format($totalKm, 2) }} km</span>
                    @endif
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
                                @if(($purposeOfTravelEnabled ?? false))
                                    <th>Purpose of Travel</th>
                                @endif
                                <th>Start Reading</th>
                                <th>End Reading</th>
                                <th>Client Present</th>
                                <th>Client Address</th>
                                <th>Started At</th>
                                <th>Ended At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $t->vehicle_name }}<br>
                                        @if(strtolower((string) ($t->vehicle_assignment_type ?? '')) === 'permanent')
                                            <small class="text-muted">Assigned Vehicle</small><br>
                                        @endif
                                        <small class="text-muted">{{ $t->registration_number }}</small>
                                    </td>
                                    <td>{{ $t->driver_name }}</td>
                                    @php
                                        $rawMode = strtolower((string) ($t->trip_mode ?? ''));
                                        $modeLabel = $rawMode === 'private' ? 'Private' : 'Business';
                                    @endphp
                                    <td>{{ $modeLabel }}</td>
                                    <td>{{ $t->customer_name_display ?: '—' }}</td>
                                    @if(($purposeOfTravelEnabled ?? false))
                                        <td>{{ $modeLabel === 'Business' ? ($t->purpose_of_travel ?: '—') : '—' }}</td>
                                    @endif
                                    @php
                                        $unit = $t->display_unit ?? (($t->tracking_mode ?? 'distance') === 'hours' ? 'hours' : 'km');
                                        $startReading = isset($t->display_start) ? $t->display_start : $t->start_km;
                                        $endReading = (isset($t->display_end) && $t->display_end !== null) ? $t->display_end : $t->end_km;
                                    @endphp
                                    <td>{{ $startReading !== null ? (number_format((float) $startReading) . ' ' . $unit) : '—' }}</td>
                                    <td>{{ $endReading !== null ? (number_format((float) $endReading) . ' ' . $unit) : '—' }}</td>
                                    <td>{{ $t->client_present ? 'Yes' : 'No' }}</td>
                                    <td>{{ $t->client_address ?: '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($t->started_at)->timezone($companyTimezone)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{ !empty($t->end_time) ? \Carbon\Carbon::parse($t->end_time)->timezone($companyTimezone)->format('d/m/Y H:i') : '—' }}
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