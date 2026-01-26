@extends('layouts.sharpfleet')

@section('title', 'Client Transport Report')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    $companyTimezone = $companyTimezone ?? config('app.timezone');
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
                        <button type="submit" class="btn btn-primary">Apply filters</button>
                        <a href="{{ url('/app/sharpfleet/admin/reports/client-transport') }}" class="btn btn-secondary">Reset</a>
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
                                    ? Carbon::parse($start, 'UTC')->timezone($companyTimezone)->format($dateFormat . ' H:i')
                                    : '-';
                                $startTimeLabel = $start
                                    ? Carbon::parse($start, 'UTC')->timezone($companyTimezone)->format($timeFormat)
                                    : '-';
                                $endTimeLabel = $endValue
                                    ? Carbon::parse($endValue, 'UTC')->timezone($companyTimezone)->format($timeFormat)
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
                                <td>{{ $trip->customer_name_display ?: '-' }}</td>
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
