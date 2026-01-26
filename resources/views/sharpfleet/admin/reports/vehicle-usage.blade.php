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
    $forceBranchScope = $forceBranchScope ?? false;
    $showBranchScope = $hasBranches || $forceBranchScope;

    $uiScope     = $scope ?? request('scope', 'company');
    $uiBranchId  = $branchId ?? request('branch_id');
    $uiStartDate = $startDate ?? request('start_date');
    $uiEndDate   = $endDate ?? request('end_date');

    /*
    |--------------------------------------------------------------------------
    | Date formatting (display only)
    |--------------------------------------------------------------------------
    */
    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y'
        : 'd/m/Y';

    $datePlaceholder = $dateFormat === 'm/d/Y'
        ? 'mm/dd/yyyy'
        : 'dd/mm/yyyy';

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

            @php
                $exportQuery = array_merge(request()->query(), ['export' => 'csv']);
                $exportUrl = url('/app/sharpfleet/admin/reports/vehicle-usage') . '?' . http_build_query($exportQuery);
            @endphp
            <a class="btn btn-primary" href="{{ $exportUrl }}">
                Export Report (CSV)
            </a>
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

                    @if(!$forceBranchScope)
                        <label class="sf-radio">
                            <input type="radio"
                                   name="scope"
                                   value="company"
                                   {{ $uiScope === 'company' ? 'checked' : '' }}>
                            <span>Company-wide</span>
                        </label>
                    @endif

                    @if($showBranchScope)
                        <label class="sf-radio">
                            <input type="radio"
                                   name="scope"
                                   value="branch"
                                   {{ $uiScope === 'branch' ? 'checked' : '' }}>
                            <span>Single branch</span>
                        </label>
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
                    <div class="sf-date-field">
                        <span class="sf-date-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
                                <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M7 3v4M17 3v4M3 9h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="text"
                               name="start_date"
                               class="form-control sf-date"
                               placeholder="{{ $datePlaceholder }}"
                               value="{{ $uiStartDate }}"
                               autocomplete="off">
                    </div>
                </div>

                <div>
                    <label class="form-label">End date</label>
                    <div class="sf-date-field">
                        <span class="sf-date-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
                                <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M7 3v4M17 3v4M3 9h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="text"
                               name="end_date"
                               class="form-control sf-date"
                               placeholder="{{ $datePlaceholder }}"
                               value="{{ $uiEndDate }}"
                               autocomplete="off">
                    </div>
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
                                        {{ $v->last_used_at ?: '—' }}
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

{{-- ================= STYLES ================= --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">

<style>
    .sf-report-card {
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 14px;
        background: #EEF3F8;
        box-shadow: 0 10px 18px rgba(10, 42, 77, 0.16);
    }

    .sf-report-select {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .sf-report-select select,
    .sf-report-select .form-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none !important;
        width: 100%;
    }

    .sf-report-select select::-ms-expand {
        display: none;
    }

    .sf-report-select::after {
        content: "";
        position: absolute;
        right: 14px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-right: 2px solid #2CBFAE;
        border-bottom: 2px solid #2CBFAE;
        transform: translateY(-50%) rotate(45deg);
        pointer-events: none;
    }

    .sf-report-select .form-select {
        border-radius: 12px;
        border: 1px solid rgba(44, 191, 174, 0.35);
        padding: 10px 44px 10px 14px;
        background-color: #f8fcfb;
        font-weight: 600;
        font-size: 0.95rem;
        color: #0A2A4D;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.85),
            0 1px 2px rgba(10, 42, 77, 0.05);
        transition:
            border-color 150ms ease,
            box-shadow 150ms ease,
            background-color 150ms ease;
        cursor: pointer;
    }

    .sf-report-select .form-select:hover {
        background-color: #ffffff;
        border-color: #2CBFAE;
    }

    .sf-report-select .form-select:focus {
        outline: none;
        background-color: #ffffff;
        border-color: #2CBFAE;
        box-shadow:
            0 0 0 3px rgba(44, 191, 174, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .sf-report-select .form-select:disabled {
        background-color: #eef2f6;
        color: rgba(10, 42, 77, 0.5);
        border-color: rgba(10, 42, 77, 0.15);
        cursor: not-allowed;
    }

    .sf-date-field {
        position: relative;
    }

    .sf-date-field .sf-date-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #2CBFAE;
        pointer-events: none;
    }

    .sf-date-field .form-control.sf-date,
    .sf-date-field .flatpickr-input.form-control {
        padding-left: 36px;
    }

    tr[data-trip-count="0"] {
        opacity: 0.65;
    }
</style>
@endpush

{{-- ================= SCRIPTS ================= --}}
@push('scripts')
<script src="https://unpkg.com/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="/app/sharpfleet/admin/reports/vehicle-usage"]');
        const scopeRadios = document.querySelectorAll('input[name="scope"]');
        const branchSelect = document.querySelector('select[name="branch_id"]');
        const dateInputs = document.querySelectorAll('.sf-date');

        function submitForm() {
            if (!form) return;
            form.submit();
        }

        function updateBranchState(scopeValue) {
            if (!branchSelect) return;
            if (scopeValue === 'branch') {
                branchSelect.disabled = false;
            } else {
                branchSelect.value = '';
                branchSelect.disabled = true;
            }
        }

        if (scopeRadios.length && branchSelect) {
            scopeRadios.forEach(function (radio) {
                radio.addEventListener('change', function (e) {
                    updateBranchState(e.target.value);
                    submitForm();
                });
            });
        }

        if (branchSelect) {
            branchSelect.addEventListener('change', submitForm);
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr('.sf-date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: '{{ $dateFormat }}',
                allowInput: true,
                onClose: function () {
                    submitForm();
                }
            });
        }

        dateInputs.forEach(function (input) {
            input.addEventListener('change', submitForm);
        });
    });
</script>
@endpush
