@extends('layouts.sharpfleet')

@section('title', 'Vehicle Usage Report')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

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

    $displayStartDate = $uiStartDate
        ? Carbon::parse($uiStartDate)->timezone($companyTimezone)->format($dateFormat)
        : '—';

    $displayEndDate = $uiEndDate
        ? Carbon::parse($uiEndDate)->timezone($companyTimezone)->format($dateFormat)
        : '—';

    /*
    |--------------------------------------------------------------------------
    | Totals (already aggregated in controller)
    |--------------------------------------------------------------------------
    */
    $totalVehicles = $summary['vehicles'] ?? 0;
    $totalTrips    = $summary['trips'] ?? 0;
    $totalDistance = $summary['distance'] ?? 0;
    $totalDuration = $summary['duration'] ?? '0h';
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Vehicle Usage Report</h1>
                <p class="page-description">
                    Understand how often vehicles are used, how far they travel, and which assets are over or under-utilised.
                </p>
            </div>

            {{-- CSV export (always full data) --}}
            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <input type="hidden" name="export" value="csv">
                <input type="hidden" name="report" value="vehicle-usage">
                <input type="hidden" name="scope" value="{{ $uiScope }}">
                <input type="hidden" name="branch_id" value="{{ $uiBranchId }}">
                <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                <input type="hidden" name="end_date" value="{{ $uiEndDate }}">

                <button type="submit" class="btn btn-primary">
                    Export Report (CSV)
                </button>
            </form>
        </div>
    </div>

    {{-- ================= CONTROLS ================= --}}
    <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/vehicle-usage') }}" class="card mb-3">
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
                            <label class="form-check-label">Branch only</label>
                        </div>
                    @endif
                </div>

                {{-- Branch selector --}}
                <div>
                    <label class="form-label">Branch</label>
                    <select name="branch_id"
                            class="form-select"
                            {{ $uiScope !== 'branch' ? 'disabled' : '' }}>
                        <option value="">All branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ (string)$uiBranchId === (string)$branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date range --}}
                <div>
                    <label class="form-label">Start date</label>
                    <input type="date"
                           name="start_date"
                           class="form-control"
                           value="{{ $uiStartDate }}">
                </div>

                <div>
                    <label class="form-label">End date</label>
                    <input type="date"
                           name="end_date"
                           class="form-control"
                           value="{{ $uiEndDate }}">
                </div>

            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-outline-primary">
                    Update Report
                </button>
            </div>

            <div class="text-muted small mt-2">
                On-screen report shows aggregated vehicle usage. CSV export always includes full trip-level data.
            </div>

        </div>
    </form>

    {{-- ================= SUMMARY ================= --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="grid grid-4 text-center">
                <div>
                    <strong>{{ $totalVehicles }}</strong><br>
                    <span class="text-muted small">Vehicles used</span>
                </div>
                <div>
                    <strong>{{ $totalTrips }}</strong><br>
                    <span class="text-muted small">Total trips</span>
                </div>
                <div>
                    <strong>{{ $totalDistance }}</strong><br>
                    <span class="text-muted small">Total distance</span>
                </div>
                <div>
                    <strong>{{ $totalDuration }}</strong><br>
                    <span class="text-muted small">Total driving time</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="card">
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
                                <th class="text-end">Trips</th>
                                <th class="text-end">Total distance</th>
                                <th class="text-end">Total driving time</th>
                                <th class="text-end">Avg / trip</th>
                                <th>Last used</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $v)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $v->vehicle_name }}<br>
                                        <small class="text-muted">{{ $v->registration_number }}</small>
                                    </td>
                                    <td class="text-end">{{ $v->trip_count }}</td>
                                    <td class="text-end">{{ $v->total_distance }}</td>
                                    <td class="text-end">{{ $v->total_duration }}</td>
                                    <td class="text-end">{{ $v->average_distance }}</td>
                                    <td>
                                        {{ $v->last_used_at
                                            ? Carbon::parse($v->last_used_at)->timezone($companyTimezone)->format($dateFormat)
                                            : '—'
                                        }}
                                    </td>
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
