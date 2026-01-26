@extends('layouts.sharpfleet')

@section('title', 'Client Transport Report')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $appTimezone = (string) (config('app.timezone') ?: 'UTC');
    $branches = $branches ?? collect();
    $customers = $customers ?? collect();
    $hasBranches = $branches->count() > 1;
    $showCustomerFilter = $ui['show_customer_filter'] ?? ($customerLinkingEnabled ?? false);
    $showBranchFilter = $ui['show_branch_filter'] ?? $hasBranches;
    $controls = $ui['controls_enabled'] ?? [];
    $allowBranchOverride = (bool) ($controls['branch'] ?? true);
    $allowCustomerOverride = (bool) ($controls['customer'] ?? true);
    $allowDateOverride = (bool) ($controls['date'] ?? true);

    $uiBranchIds = collect($ui['branch_ids'] ?? request('branch_ids', []))
        ->filter(fn ($id) => is_numeric($id))
        ->map(fn ($id) => (string) (int) $id)
        ->values()
        ->all();

    $uiCustomerId = $ui['customer_id'] ?? request('customer_id');
    $uiStartDate = $ui['start_date'] ?? request('start_date');
    $uiEndDate   = $ui['end_date'] ?? request('end_date');

    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y'
        : 'd/m/Y';

    $datePlaceholder = $dateFormat === 'm/d/Y'
        ? 'mm/dd/yyyy'
        : 'dd/mm/yyyy';

    $timeFormat = 'H:i';

    $clientLabel = $clientPresenceLabel ?? 'Client / Customer';
@endphp

<div class="container">
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Client Transport Report</h1>
                <p class="page-description">
                    Client-level trip summary with date, timing, driver, vehicle, and distance details.
                </p>
            </div>

            @php
                $pdfQuery = request()->query();
                if (count($uiBranchIds) > 0) {
                    $pdfQuery['branch_ids'] = $uiBranchIds;
                }
                if ($uiCustomerId) {
                    $pdfQuery['customer_id'] = $uiCustomerId;
                }
                if ($uiStartDate) {
                    $pdfQuery['start_date'] = $uiStartDate;
                }
                if ($uiEndDate) {
                    $pdfQuery['end_date'] = $uiEndDate;
                }
                $pdfUrl = url('/app/sharpfleet/admin/reports/client-transport/pdf') . '?' . http_build_query($pdfQuery);
            @endphp

            <div class="flex" style="display:flex; gap:10px; align-items:center;">
                <a class="btn btn-primary" href="{{ $pdfUrl }}">Export PDF</a>

                <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/client-transport') }}">
                    <input type="hidden" name="export" value="csv">
                    @if(count($uiBranchIds) > 0)
                        @foreach($uiBranchIds as $branchId)
                            <input type="hidden" name="branch_ids[]" value="{{ $branchId }}">
                        @endforeach
                    @else
                        <input type="hidden" name="branch_ids[]" value="">
                    @endif
                    <input type="hidden" name="customer_id" value="{{ $uiCustomerId }}">
                    <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                    <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                    <button type="submit" class="btn btn-primary">Export CSV</button>
                </form>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/client-transport') }}">
        <div class="card sf-report-card mb-3">
            <div class="card-body">
                <div class="grid grid-3 align-end">
                    <div>
                        <label class="form-label">Branch</label>
                        <div class="sf-report-select">
                            <select name="branch_ids[]" class="form-select" {{ ($showBranchFilter && $allowBranchOverride) ? '' : 'disabled' }}>
                                <option value="">All branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ in_array((string) $branch->id, $uiBranchIds, true) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">{{ $clientLabel }}</label>
                        <div class="sf-report-select">
                            <select name="customer_id" class="form-select" {{ ($showCustomerFilter && $allowCustomerOverride) ? '' : 'disabled' }}>
                                <option value="">All {{ $clientLabel }}s</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ (string) $uiCustomerId === (string) $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(!$showCustomerFilter)
                            <div class="text-muted small mt-1">
                                Customer linking is disabled for this company.
                            </div>
                        @endif
                    </div>

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
                                   autocomplete="off"
                                   {{ $allowDateOverride ? '' : 'disabled' }}>
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
                                   autocomplete="off"
                                   {{ $allowDateOverride ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>

                <div class="flex-between mt-4">
                    <div class="text-muted small">
                        On-screen view matches exported data.
                        No columns are omitted in the CSV export.
                    </div>
                    <div class="flex" style="gap: 10px;">
                        <button type="submit" class="btn-sf-navy">Apply filters</button>
                        <a href="{{ url('/app/sharpfleet/admin/reports/client-transport') }}" class="btn-sf-navy">Reset</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card sf-report-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ $clientLabel }}</th>
                            <th>Date/Time</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Trip Purpose</th>
                            <th>Distance (km)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $trip)
                            @php
                                $start = $trip->started_at ?? null;
                                $endValue = $trip->end_time ?? $trip->ended_at ?? null;

                                $dateTimeLabel = $start
                                    ? Carbon::parse($start, $appTimezone)->timezone($companyTimezone)->format($dateFormat . ' H:i')
                                    : '-';
                                $startTimeLabel = $start
                                    ? Carbon::parse($start, $appTimezone)->timezone($companyTimezone)->format($timeFormat)
                                    : '-';
                                $endTimeLabel = $endValue
                                    ? Carbon::parse($endValue, $appTimezone)->timezone($companyTimezone)->format($timeFormat)
                                    : '-';

                                $distanceLabel = '-';
                                if (isset($trip->start_km, $trip->end_km) && is_numeric($trip->start_km) && is_numeric($trip->end_km)) {
                                    $delta = (float) $trip->end_km - (float) $trip->start_km;
                                    if ($delta >= 0) {
                                        $distanceLabel = number_format($delta, 1);
                                    }
                                }

                                $tripPurpose = '';
                                if ($purposeOfTravelEnabled ?? false) {
                                    $rawMode = strtolower((string) ($trip->trip_mode ?? ''));
                                    $isBusiness = $rawMode !== 'private';
                                    $tripPurpose = $isBusiness ? ($trip->purpose_of_travel ?? '') : '';
                                }
                            @endphp
                            <tr>
                                <td>{{ $trip->client_name_display ?: '-' }}</td>
                                <td>{{ $dateTimeLabel }}</td>
                                <td>{{ $startTimeLabel }}</td>
                                <td>{{ $endTimeLabel }}</td>
                                <td>{{ $trip->vehicle_name }}</td>
                                <td>{{ $trip->driver_name }}</td>
                                <td>{{ $tripPurpose }}</td>
                                <td>{{ $distanceLabel }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-muted">No trips found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

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

@push('scripts')
<script src="https://unpkg.com/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="/app/sharpfleet/admin/reports/client-transport"]');
        const branchSelect = document.querySelector('select[name="branch_ids[]"]');
        const customerSelect = document.querySelector('select[name="customer_id"]');

        function submitForm() {
            if (!form) return;
            form.submit();
        }

        if (branchSelect) {
            branchSelect.addEventListener('change', submitForm);
        }

        if (customerSelect) {
            customerSelect.addEventListener('change', submitForm);
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
    });
</script>
@endpush
