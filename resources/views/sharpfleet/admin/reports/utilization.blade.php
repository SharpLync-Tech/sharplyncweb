@extends('layouts.sharpfleet')

@section('title', 'Utilization Report')

@section('sharpfleet-content')

@php
    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $branches = $branches ?? collect();
    $vehicles = $vehicles ?? collect();
    $hasBranches = $branches->count() > 1;

    $uiScope = request('scope', $scope ?? 'company');
    $uiBranchId = request('branch_id', $branchId ?? '');
    $uiVehicleId = request('vehicle_id', $vehicleId ?? '');
    $uiPeriod = request('period', $period ?? 'month');
    $uiPeriodDate = request('period_date', $periodDate ?? '');

    $uiAvailabilityPreset = request('availability_preset', $availabilityPreset ?? 'business_hours');
    $uiAvailabilityDays = collect(request('availability_days', $availabilityDays ?? ['1','2','3','4','5']))
        ->map(fn ($d) => (string) $d)->all();
    $uiWorkStart = request('work_start', $workStart ?? '07:00');
    $uiWorkEnd = request('work_end', $workEnd ?? '17:00');

    $dateFormat = $dateFormat ?? (str_starts_with($companyTimezone, 'America/') ? 'm/d/Y' : 'd/m/Y');
    $datePlaceholder = $dateFormat === 'm/d/Y' ? 'mm/dd/yyyy' : 'dd/mm/yyyy';
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Utilization Report</h1>
                <p class="page-description">
                    Track vehicle utilization over time, highlighting underused and heavily used assets.
                </p>
            </div>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <form method="GET"
          action="{{ url('/app/sharpfleet/admin/reports/utilization') }}"
          class="card sf-report-card mb-3">

        <div class="card-body">

            {{-- ===== ROW 1: WHAT ===== --}}
            <div class="grid grid-3 mb-3">

                {{-- Scope --}}
                <div>
                    <label class="form-label">Scope</label>
                    <label class="sf-radio">
                        <input type="radio" name="scope" value="company" {{ $uiScope === 'company' ? 'checked' : '' }}>
                        <span>Company-wide</span>
                    </label>
                    @if($hasBranches)
                        <label class="sf-radio">
                            <input type="radio" name="scope" value="branch" {{ $uiScope === 'branch' ? 'checked' : '' }}>
                            <span>Single branch</span>
                        </label>
                    @endif
                </div>

                {{-- Branch --}}
                <div>
                    <label class="form-label">Branch</label>
                    <div class="sf-report-select">
                        <select name="branch_id" class="form-select" {{ $uiScope !== 'branch' ? 'disabled' : '' }}>
                            <option value="">All branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ (string)$uiBranchId === (string)$branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Vehicle --}}
                <div>
                    <label class="form-label">Vehicle</label>
                    <div class="sf-report-select">
                        <select name="vehicle_id" class="form-select">
                            <option value="">All vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    {{ (string)$uiVehicleId === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }}
                                    {{ $vehicle->registration_number ? '(' . $vehicle->registration_number . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            {{-- ===== ROW 2: WHEN ===== --}}
            <div class="grid grid-3 mb-3 align-end">

                {{-- Period --}}
                <div>
                    <label class="form-label">Period</label>
                    <div class="sf-period-toggle">
                        <label>
                            <input type="radio" name="period" value="day" {{ $uiPeriod === 'day' ? 'checked' : '' }}>
                            <span>Day</span>
                        </label>
                        <label>
                            <input type="radio" name="period" value="week" {{ $uiPeriod === 'week' ? 'checked' : '' }}>
                            <span>Week</span>
                        </label>
                        <label>
                            <input type="radio" name="period" value="month" {{ $uiPeriod === 'month' ? 'checked' : '' }}>
                            <span>Month</span>
                        </label>
                    </div>
                </div>

                {{-- Date --}}
                <div>
                    <label class="form-label" id="sfPeriodLabel">Select period</label>
                    <div class="sf-date-field">
                        <span class="sf-date-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
                                <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M7 3v4M17 3v4M3 9h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="text"
                               name="period_date"
                               class="form-control sf-date"
                               placeholder="{{ $datePlaceholder }}"
                               value="{{ $uiPeriodDate }}"
                               autocomplete="off">
                    </div>
                </div>

            </div>

            {{-- ===== ROW 3: AVAILABILITY ===== --}}
            <div class="grid grid-3 align-end">

                <div>
                    <label class="form-label">Availability</label>
                    <div class="sf-report-select">
                        <select name="availability_preset" class="form-select" id="sfAvailabilityPreset">
                            <option value="business_hours" {{ $uiAvailabilityPreset === 'business_hours' ? 'selected' : '' }}>
                                Business hours (Mon–Fri)
                            </option>
                            <option value="24_7" {{ $uiAvailabilityPreset === '24_7' ? 'selected' : '' }}>
                                24/7
                            </option>
                            <option value="custom" {{ $uiAvailabilityPreset === 'custom' ? 'selected' : '' }}>
                                Custom (advanced)
                            </option>
                        </select>
                    </div>
                    <div class="text-muted small mt-1" id="sfAvailabilitySummary">
                        7:00am – 5:00pm, Monday to Friday
                    </div>
                </div>

                <div class="sf-advanced-wrap">
                    <button type="button" class="btn-sf-navy" id="sfAdvancedToggle">
                        Advanced availability
                    </button>
                </div>

            </div>

            {{-- ===== ADVANCED PANEL (UNCHANGED) ===== --}}
            <div class="sf-advanced-panel mt-3 {{ $uiAvailabilityPreset === 'custom' ? 'is-open' : '' }}" id="sfAdvancedPanel">
                <div class="grid grid-2">
                    <div>
                        <label class="form-label">Working days</label>
                        <div class="sf-days">
                            @foreach([
                                '1' => 'Mon','2' => 'Tue','3' => 'Wed','4' => 'Thu',
                                '5' => 'Fri','6' => 'Sat','0' => 'Sun'
                            ] as $dayValue => $dayLabel)
                                <label class="sf-check">
                                    <input type="checkbox" name="availability_days[]"
                                           value="{{ $dayValue }}"
                                           {{ in_array((string)$dayValue, $uiAvailabilityDays, true) ? 'checked' : '' }}>
                                    <span>{{ $dayLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Working hours</label>
                        <div class="sf-time-grid">
                            <div>
                                <label class="form-label small">Start time</label>
                                <input type="time" name="work_start" class="form-control" value="{{ $uiWorkStart }}">
                            </div>
                            <div>
                                <label class="form-label small">End time</label>
                                <input type="time" name="work_end" class="form-control" value="{{ $uiWorkEnd }}">
                            </div>
                        </div>
                        <div class="text-muted small mt-2">
                            Public holiday exclusion coming soon.
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== APPLY ===== --}}
            <div class="mt-3 text-end">
                <button type="submit" class="btn-sf-navy">Apply</button>
            </div>

        </div>
    </form>

    {{-- ================= EVERYTHING BELOW UNCHANGED ================= --}}
    {{-- Summary cards, export button, table, styles, scripts remain exactly as-is --}}

@endsection
