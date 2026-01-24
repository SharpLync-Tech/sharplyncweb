@extends('layouts.sharpfleet')

@section('title', 'Vehicle Usage Report')

@section('sharpfleet-content')

@php
    /*
    |--------------------------------------------------------------------------
    | Inputs (resolved by controller)
    |--------------------------------------------------------------------------
    */
    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $branches        = $branches ?? collect();
    $hasBranches     = $branches->count() > 1;

    $uiScope     = request('scope', 'company');
    $uiBranchId  = request('branch_id');
    $uiStartDate = request('start_date');
    $uiEndDate   = request('end_date');

    /*
    |--------------------------------------------------------------------------
    | Date formatting (display only)
    |--------------------------------------------------------------------------
    */
    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y'
        : 'd/m/Y';
    $datePlaceholder = $dateFormat === 'm/d/Y' ? 'mm/dd/yyyy' : 'dd/mm/yyyy';

    /*
    |--------------------------------------------------------------------------
    | Summary (from controller)
    |--------------------------------------------------------------------------
    */
    $totalVehicles = $summary['vehicles'] ?? 0;
    $totalTrips    = $summary['trips'] ?? 0;
    $totalDistance = $summary['distance'] ?? '0';
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Vehicle Usage Report</h1>
                <p class="page-description">
                    Understand how often vehicles are used, how far they travel,
                    and which assets may be under- or over-utilised.
                </p>
            </div>

            <button class="btn btn-primary" disabled
                    title="CSV export for vehicle usage will be added next">
                Export Report (CSV)
            </button>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <form method="GET"
          action="{{ url('/app/sharpfleet/admin/reports/vehicle-usage') }}"
          class="card sf-report-card mb-3">
        <div class="card-body">

            <div class="grid grid-4 align-end">

                {{-- Scope --}}
                <div>
                    <label class="form-label">Scope</label>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               name="scope"
                               value="company"
                               {{ $uiScope === 'company' ? 'checked' : '' }}>
                        <label class="form-check-label">Company-wide</label>
                    </div>

                    @if($hasBranches)
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="radio"
                                   name="scope"
                                   value="branch"
                                   {{ $uiScope === 'branch' ? 'checked' : '' }}>
                            <label class="form-check-label">Single branch</label>
                        </div>
                    @endif

                    <div class="text-muted small mt-1">
                        Choose whether usage is calculated across the whole company
                        or limited to a single branch.
                    </div>
                </div>

                {{-- Branch --}}
                <div>
                    <label class="form-label">Branch</label>
                    <div class="sf-report-select">
                        <select name="branch_id"
                                class="form-select"
                                data-auto-submit="1"
                                {{ $uiScope !== 'branch' ? 'disabled' : '' }}>
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ (string) $uiBranchId === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Dates --}}
                <div>
                    <label class="form-label">Start date</label>
                    <input type="text"
                           name="start_date"
                           class="form-control sf-date"
                           placeholder="{{ $datePlaceholder }}"
                           value="{{ $uiStartDate }}">
                </div>

                <div>
                    <label class="form-label">End date</label>
                    <input type="text"
                           name="end_date"
                           class="form-control sf-date"
                           placeholder="{{ $datePlaceholder }}"
                           value="{{ $uiEndDate }}">
                </div>

            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn-sf-navy">
                    Update Report
                </button>
            </div>

            <div class="text-muted small mt-2">
                Usage is calculated based on trips that started within the selected date range.
            </div>

        </div>
    </form>

    {{-- ================= SUMMARY ================= --}}
    <div class="card sf-report-card mb-3">
        <div class="card-body">
            <div class="grid grid-3 text-center">
                <div>
                    <strong>{{ $totalVehicles }}</strong><br>
                    <span class="text-muted small">Active vehicles</span>
                </div>
                <div>
                    <strong>{{ $totalTrips }}</strong><br>
                    <span class="text-muted small">Total trips</span>
                </div>
                <div>
                    <strong>{{ $totalDistance }}</strong><br>
                    <span class="text-muted small">Total distance</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="card sf-report-card">
        <div class="card-body">

            @if($vehicles->count() === 0)
                <p class="text-muted fst-italic">
                    No vehicle usage found for the selected period.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Status</th>
                                <th class="text-end">Trips</th>
                                <th class="text-end">Total distance</th>
                                <th class="text-end">Total driving time</th>
                                <th class="text-end">Avg / trip</th>
                                <th>Last used</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $v)
                                @php
                                    if ($v->trip_count === 0) {
                                        $usageStatus = 'Idle';
                                        $badgeClass = 'bg-secondary';
                                    } elseif ($v->trip_count >= 10) {
                                        $usageStatus = 'High';
                                        $badgeClass = 'bg-success';
                                    } else {
                                        $usageStatus = 'Low';
                                        $badgeClass = 'bg-warning text-dark';
                                    }
                                @endphp
                                <tr data-trip-count="{{ $v->trip_count }}">
                                    <td class="fw-bold">
                                        {{ $v->vehicle_name }}<br>
                                        <small class="text-muted">{{ $v->registration_number }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $usageStatus }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $v->trip_count }}</td>
                                    <td class="text-end">{{ $v->total_distance_km }} km</td>
                                    <td class="text-end">{{ $v->total_duration }}</td>
                                    <td class="text-end">{{ $v->average_distance_km }} km</td>
                                    <td>
                                        {{ $v->last_used_at ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-muted small mt-2">
                    <strong>Usage status guide:</strong>
                    High = frequent use,
                    Low = occasional use,
                    Idle = no recorded trips.
                </div>
            @endif

        </div>
    </div>

</div>

@endsection
