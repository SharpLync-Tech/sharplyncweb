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
    $datePlaceholder = $dateFormat === 'm/d/Y' ? 'mm/dd/yyyy' : 'dd/mm/yyyy';

    /*
    |--------------------------------------------------------------------------
    | Summary (from controller)
    |--------------------------------------------------------------------------
    */
    $totalVehicles = $summary['vehicles'] ?? 0;
    $totalTrips    = $summary['trips'] ?? 0;
    $totalDistance = $summary['distance'] ?? '0 km';
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Vehicle Usage Report</h1>
                <p class="page-description">
                    See how often vehicles are used, how far they travel, and which assets may be over- or under-utilised.
                </p>
            </div>

            {{-- CSV export (to be wired properly later) --}}
            <button class="btn btn-outline-secondary" disabled
                    title="CSV export for vehicle usage will be added next">
                Export Report (CSV)
            </button>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <form method="GET"
          action="{{ url('/app/sharpfleet/admin/reports/vehicle-usage') }}"
          class="card mb-3">
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
                <button type="submit" class="btn btn-outline-primary">
                    Update Report
                </button>
            </div>

            <div class="text-muted small mt-2">
                This on-screen report shows aggregated vehicle usage.
                CSV export will include full trip-level data.
            </div>

        </div>
    </form>

    {{-- ================= SUMMARY ================= --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="grid grid-3 text-center">
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
                                    <td class="text-end">{{ $v->total_distance_km }} km</td>
                                    <td class="text-end">{{ $v->total_duration }}</td>
                                    <td class="text-end">{{ $v->average_distance_km }} km</td>
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

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">
<style>
    .sf-report-select {
        position: relative;
    }
    .sf-report-select::after {
        content: "";
        position: absolute;
        right: 14px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-right: 2px solid rgba(10, 42, 77, 0.6);
        border-bottom: 2px solid rgba(10, 42, 77, 0.6);
        transform: translateY(-50%) rotate(45deg);
        pointer-events: none;
    }
    .sf-report-select .form-select {
        appearance: none;
        border-radius: 10px;
        border: 1px solid rgba(10, 42, 77, 0.2);
        padding: 10px 36px 10px 12px;
        background: #f7fafc;
        font-weight: 600;
        color: #0A2A4D;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }
    .sf-report-select .form-select:focus {
        border-color: rgba(57, 183, 170, 0.6);
        box-shadow: 0 0 0 3px rgba(57, 183, 170, 0.18);
    }
    .sf-report-select .form-select:disabled {
        background: #eef2f6;
        color: rgba(10, 42, 77, 0.5);
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/flatpickr"></script>
<script>
    (function () {
        if (typeof flatpickr === 'undefined') return;
        const displayFormat = @json($dateFormat);
        const displayPlaceholder = @json($datePlaceholder);

        flatpickr('.sf-date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: displayFormat,
            allowInput: true,
            onReady: function (selectedDates, dateStr, instance) {
                if (instance && instance.altInput) {
                    instance.altInput.placeholder = displayPlaceholder;
                }
            },
        });
    })();
</script>
<script>
    (function () {
        const form = document.querySelector('form[action="{{ url('/app/sharpfleet/admin/reports/vehicle-usage') }}"]');
        if (!form) return;

        form.querySelectorAll('select[data-auto-submit="1"]').forEach((select) => {
            select.addEventListener('change', () => form.submit());
        });

        form.querySelectorAll('input[type="radio"][name="scope"]').forEach((radio) => {
            radio.addEventListener('change', () => form.submit());
        });
    })();
</script>
@endpush

@endsection
