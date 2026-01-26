@extends('layouts.sharpfleet')

@section('title', 'Trips & Compliance Report')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | Core inputs (resolved by controller)
    |--------------------------------------------------------------------------
    */
    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $branches = $branches ?? collect();
    $vehicles = $vehicles ?? collect();
    $hasBranches = $branches->count() > 0;

    /*
    |--------------------------------------------------------------------------
    | UI state
    |--------------------------------------------------------------------------
    */
    $uiScope = $ui['scope'] ?? request('scope', 'company');
    $uiBranchId = $ui['branch_id'] ?? request('branch_id');
    $uiBranchIds = collect($ui['branch_ids'] ?? request('branch_ids', []))
        ->filter(fn ($id) => is_numeric($id))
        ->map(fn ($id) => (string) (int) $id)
        ->values()
        ->all();

    if (!$uiBranchId && count($uiBranchIds) > 0) {
        $uiBranchId = $uiBranchIds[0];
    }
    $uiVehicleId = $ui['vehicle_id'] ?? request('vehicle_id');
    $uiStartDate = $ui['start_date'] ?? request('start_date');
    $uiEndDate   = $ui['end_date'] ?? request('end_date');

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
    | Summary
    |--------------------------------------------------------------------------
    */
    $totalTrips = $trips->count();
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Trips & Compliance Report</h1>
                <p class="page-description">
                    Detailed trip-level vehicle usage report suitable for regulatory compliance,
                    accountant review, and audit evidence.
                </p>
            </div>

            @php
                $pdfQuery = request()->query();
                $pdfQuery['scope'] = $uiScope;
                $pdfQuery['branch_id'] = $uiBranchId;
                if (count($uiBranchIds) > 0) {
                    $pdfQuery['branch_ids'] = $uiBranchIds;
                } elseif ($uiBranchId) {
                    $pdfQuery['branch_ids'] = [$uiBranchId];
                }
                $pdfUrl = url('/app/sharpfleet/admin/reports/trips/pdf') . '?' . http_build_query($pdfQuery);
            @endphp

            <div class="flex" style="display:flex; gap:10px; align-items:center;">
                <a class="btn btn-outline-primary" href="{{ $pdfUrl }}">Export PDF</a>

                <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <input type="hidden" name="export" value="csv">
                <input type="hidden" name="scope" value="{{ $uiScope }}">
                @if(count($uiBranchIds) > 0)
                    @foreach($uiBranchIds as $branchId)
                        <input type="hidden" name="branch_ids[]" value="{{ $branchId }}">
                    @endforeach
                @elseif($uiBranchId)
                    <input type="hidden" name="branch_ids[]" value="{{ $uiBranchId }}">
                @endif
                <input type="hidden" name="branch_id" value="{{ $uiBranchId }}">
                <input type="hidden" name="vehicle_id" value="{{ $uiVehicleId }}">
                <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                <button type="submit" class="btn btn-primary">
                    Export CSV
                </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
        <div class="card sf-report-card mb-3">
            <div class="card-body">

                <div class="grid grid-4 align-end">

                    {{-- Scope --}}
                    <div>
                        <label class="form-label">Scope</label>

                        <div class="sf-radio-row">
                            <label class="sf-radio">
                                <input type="radio"
                                       name="scope"
                                       value="company"
                                       {{ $uiScope === 'company' ? 'checked' : '' }}>
                                <span>Company-wide</span>
                            </label>

                            @if($hasBranches)
                                <label class="sf-radio">
                                    <input type="radio"
                                           name="scope"
                                           value="branch"
                                           {{ $uiScope === 'branch' ? 'checked' : '' }}>
                                    <span>Single branch</span>
                                </label>
                            @endif
                        </div>

                        <div class="text-muted small mt-1">
                            Choose whether trips are reported across the whole company
                            or limited to a single branch.
                        </div>
                    </div>

                    {{-- Branch --}}
                    <div>
                        <label class="form-label">Branch</label>
                        <div class="sf-report-select">
                            <select name="branch_id"
                                    class="form-select"
                                    {{ ($uiScope !== 'branch' || !$hasBranches) ? 'disabled' : '' }}>
                                <option value="">All branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ (string) $uiBranchId === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="branch_ids[]" value="">
                    </div>

                    {{-- Vehicle --}}
                    <div>
                        <label class="form-label">Vehicle</label>
                        <div class="sf-report-select">
                            <select name="vehicle_id" class="form-select">
                                <option value="">All vehicles</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}"
                                        {{ (string) $uiVehicleId === (string) $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->name }} {{ $vehicle->registration_number ? '(' . $vehicle->registration_number . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Date range --}}
                    <div>
                        <label class="form-label">Date range</label>
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
                        <div class="sf-date-field mt-2">
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

                <div class="flex-between mt-4">
                    <div class="text-muted small">
                        On-screen view matches exported data.
                        No columns are omitted in the CSV export.
                    </div>

                    <button type="submit" class="btn-sf-navy">
                        Update Report
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- ================= SUMMARY ================= --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="grid grid-3 text-center">
                <div>
                    <strong>{{ $totalTrips }}</strong><br>
                    <span class="text-muted small">Trips in report</span>
                </div>
                <div>
                    @php
                        $uiStartLabel = $uiStartDate
                            ? Carbon::parse($uiStartDate, 'UTC')->timezone($companyTimezone)->format($dateFormat)
                            : '-';
                        $uiEndLabel = $uiEndDate
                            ? Carbon::parse($uiEndDate, 'UTC')->timezone($companyTimezone)->format($dateFormat)
                            : '-';
                    @endphp
                    <strong>{{ $uiStartLabel }} - {{ $uiEndLabel }}</strong><br>
                    <span class="text-muted small">Reporting period</span>
                </div>
                <div>
                    <strong>Trip-level detail</strong><br>
                    <span class="text-muted small">Audit-ready format</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="card">
        <div class="card-body">

            @if($trips->count() === 0)
                <p class="text-muted fst-italic">
                    No trips found for the selected period.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Vehicle</th>
                                <th>Registration</th>
                                <th>Driver</th>
                                <th>{{ $clientPresenceLabel ?? 'Client / Customer' }}</th>
                                <th class="text-end">Start odometer</th>
                                <th class="text-end">End odometer</th>
                                <th class="text-end">Distance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                @php
                                    $unit = isset($t->display_unit)
                                        ? (string) $t->display_unit
                                        : (((string) ($t->tracking_mode ?? 'distance')) === 'hours' ? 'hours' : 'km');
                                    $startReading = $t->display_start ?? $t->start_km ?? null;
                                    $endReading = $t->display_end ?? $t->end_km ?? null;
                                    $distanceLabel = null;
                                    if ($startReading !== null && $endReading !== null && is_numeric($startReading) && is_numeric($endReading)) {
                                        $delta = (float) $endReading - (float) $startReading;
                                        if ($delta >= 0) {
                                            $labelUnit = $unit === 'hours' ? 'h' : $unit;
                                            $distanceLabel = number_format($delta, 1) . ' ' . $labelUnit;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        {{ Carbon::parse($t->start_time ?? $t->started_at, 'UTC')->timezone($companyTimezone)->format($dateFormat) }}
                                    </td>

                                    <td class="fw-bold">
                                        {{ $t->vehicle_name }}
                                    </td>

                                    <td>{{ $t->registration_number ?: '-' }}</td>

                                    <td>{{ $t->driver_name ?: '-' }}</td>

                                    <td>{{ $t->customer_name_display ?: '-' }}</td>

                                    <td class="text-end">
                                        {{ $startReading !== null && $startReading !== '' ? $startReading . ' ' . ($unit === 'hours' ? 'h' : $unit) : '-' }}
                                    </td>

                                    <td class="text-end">
                                        {{ $endReading !== null && $endReading !== '' ? $endReading . ' ' . ($unit === 'hours' ? 'h' : $unit) : '-' }}
                                    </td>

                                    <td class="text-end">{{ $distanceLabel ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="text-muted small mt-3 text-center">
        This report is system-generated and reflects recorded trip data
        at the time of export.
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

    .sf-radio-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .sf-radio {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
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

    .sf-date.form-control,
    .flatpickr-input.form-control {
        border-radius: 12px;
        border: 1px solid rgba(44, 191, 174, 0.35);
        padding: 10px 14px;
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

    .sf-date.form-control:hover,
    .flatpickr-input.form-control:hover {
        background-color: #ffffff;
        border-color: #2CBFAE;
    }

    .sf-date.form-control:focus,
    .flatpickr-input.form-control:focus {
        outline: none;
        background-color: #ffffff;
        border-color: #2CBFAE;
        box-shadow:
            0 0 0 3px rgba(44, 191, 174, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }
</style>
@endpush

{{-- ================= SCRIPTS ================= --}}
@push('scripts')
<script src="https://unpkg.com/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="/app/sharpfleet/admin/reports/trips"]');
        const scopeRadios = document.querySelectorAll('input[name="scope"]');
        const branchSelect = document.querySelector('select[name="branch_id"]');
        const branchIdsHidden = document.querySelector('input[name="branch_ids[]"]');
        const vehicleSelect = document.querySelector('select[name="vehicle_id"]');

        function submitForm() {
            if (!form) return;
            form.submit();
        }

        const hasBranches = {{ $hasBranches ? 'true' : 'false' }};

        function updateBranchState(value) {
            if (!branchSelect) return;
            if (!hasBranches) {
                branchSelect.disabled = true;
                return;
            }
            if (value === 'branch') {
                branchSelect.disabled = false;
            } else {
                branchSelect.value = '';
                branchSelect.disabled = true;
            }
        }

        function syncBranchIds() {
            if (!branchIdsHidden) return;
            const scope = document.querySelector('input[name="scope"]:checked');
            const scopeValue = scope ? scope.value : 'company';
            if (scopeValue === 'branch' && branchSelect && branchSelect.value) {
                branchIdsHidden.value = branchSelect.value;
            } else {
                branchIdsHidden.value = '';
            }
        }

        scopeRadios.forEach(function (radio) {
            radio.addEventListener('change', function (e) {
                updateBranchState(e.target.value);
                syncBranchIds();
                submitForm();
            });
        });

        if (branchSelect) {
            branchSelect.addEventListener('change', function () {
                syncBranchIds();
                submitForm();
            });
        }

        if (vehicleSelect) {
            vehicleSelect.addEventListener('change', submitForm);
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr('.sf-date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: '{{ $dateFormat }}',
                allowInput: true,
                onClose: function () {
                    syncBranchIds();
                    submitForm();
                }
            });
        }

        const initialScope = document.querySelector('input[name="scope"]:checked');
        updateBranchState(initialScope ? initialScope.value : '{{ $uiScope }}');
        syncBranchIds();
    });
</script>
@endpush
