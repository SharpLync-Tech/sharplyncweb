@extends('layouts.sharpfleet')

@section('title', 'Utilization Report')

@section('sharpfleet-content')

@php
    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $branches = $branches ?? collect();
    $vehicles = $vehicles ?? collect();
    $hasBranches = $branches->count() > 1;
    $forceBranchScope = $forceBranchScope ?? false;
    $showBranchScope = $hasBranches || $forceBranchScope;

    $uiScope = $scope ?? request('scope', 'company');
    $uiBranchId = $branchId ?? request('branch_id', '');
    $uiVehicleId = $vehicleId ?? request('vehicle_id', '');
    $uiPeriod = $period ?? request('period', 'month');
    $uiPeriodDate = $periodDate ?? request('period_date', '');

    $uiAvailabilityPreset = $availabilityPreset ?? request('availability_preset', 'business_hours');
    $uiAvailabilityDays = collect($availabilityDays ?? request('availability_days', ['1','2','3','4','5']))->map(fn ($d) => (string) $d)->all();
    $uiWorkStart = $workStart ?? request('work_start', '07:00');
    $uiWorkEnd = $workEnd ?? request('work_end', '17:00');

    $dateFormat = $dateFormat ?? (str_starts_with($companyTimezone, 'America/') ? 'm/d/Y' : 'd/m/Y');
    $datePlaceholder = $dateFormat === 'm/d/Y' ? 'mm/dd/yyyy' : 'dd/mm/yyyy';
@endphp

<div class="container">
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

    <form method="GET"
          action="{{ url('/app/sharpfleet/admin/reports/utilization') }}"
          class="card sf-report-card mb-3">

        <div class="card-body">
            <div class="grid grid-4 align-end">
                <div>
                    <label class="form-label">Scope</label>
                    @if(!$forceBranchScope)
                        <label class="sf-radio">
                            <input type="radio" name="scope" value="company" {{ $uiScope === 'company' ? 'checked' : '' }}>
                            <span>Company-wide</span>
                        </label>
                    @endif
                    @if($showBranchScope)
                        <label class="sf-radio">
                            <input type="radio" name="scope" value="branch" {{ $uiScope === 'branch' ? 'checked' : '' }}>
                            <span>Single branch</span>
                        </label>
                    @endif

                    <div class="mt-2">
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
                </div>

                <div>
                    <label class="form-label">Branch</label>
                    <div class="sf-report-select">
                        <select name="branch_id" class="form-select" {{ $uiScope !== 'branch' ? 'disabled' : '' }}>
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
            </div>

            <div class="grid grid-3 align-end mt-3 sf-period-row">
                <div class="sf-date-col">
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

                <div></div>
                <div class="sf-availability-col">
                    <label class="form-label">Availability</label>
                    <div class="sf-report-select">
                        <select name="availability_preset" class="form-select" id="sfAvailabilityPreset">
                            <option value="business_hours" {{ $uiAvailabilityPreset === 'business_hours' ? 'selected' : '' }}>
                                Business hours (Mon-Fri)
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
                        7:00am - 5:00pm, Monday to Friday
                    </div>
                </div>
            </div>

            <div class="sf-advanced-panel mt-3 {{ $uiAvailabilityPreset === 'custom' ? 'is-open' : '' }}" id="sfAdvancedPanel">
                <div class="grid grid-2">
                    <div>
                        <label class="form-label">Working days</label>
                        <div class="sf-days">
                            @foreach([
                                '1' => 'Mon',
                                '2' => 'Tue',
                                '3' => 'Wed',
                                '4' => 'Thu',
                                '5' => 'Fri',
                                '6' => 'Sat',
                                '0' => 'Sun'
                            ] as $dayValue => $dayLabel)
                                <label class="sf-check">
                                    <input type="checkbox" name="availability_days[]"
                                           value="{{ $dayValue }}"
                                           {{ in_array((string) $dayValue, $uiAvailabilityDays, true) ? 'checked' : '' }}>
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
                                <input type="time" name="work_start" class="form-control sf-time" value="{{ $uiWorkStart }}">
                            </div>
                            <div>
                                <label class="form-label small">End time</label>
                                <input type="time" name="work_end" class="form-control sf-time" value="{{ $uiWorkEnd }}">
                            </div>
                        </div>
                        <div class="text-muted small mt-2">
                            Public holiday exclusion coming soon.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 sf-apply-row">
                <button type="submit" class="btn-sf-navy">Apply</button>
                <button type="button" class="btn-sf-navy" id="sfAdvancedToggle">Advanced availability</button>
            </div>
        </div>
    </form>

    <div class="card sf-report-card mb-3">
        <div class="card-body">
            <div class="grid grid-4 text-center">
                <div>
                    <strong>{{ number_format($averageUtilization, 1) }}%</strong><br>
                    <span class="text-muted small">Average utilization</span>
                </div>
                <div>
                    <strong>{{ $underUtilisedCount }}</strong><br>
                    <span class="text-muted small">Under-utilised vehicles</span>
                </div>
                <div>
                    <strong>{{ $overUtilisedCount }}</strong><br>
                    <span class="text-muted small">Over-utilised vehicles</span>
                </div>
                <div>
                    <strong>{{ number_format($totalUsedHours, 1) }} h</strong><br>
                    <span class="text-muted small">Total hours used</span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mb-3">
        @php
            $exportQuery = array_merge(request()->query(), ['export' => 'csv']);
            $exportUrl = url('/app/sharpfleet/admin/reports/utilization') . '?' . http_build_query($exportQuery);
        @endphp
        <a href="{{ $exportUrl }}" class="btn btn-primary">Export CSV</a>
    </div>

    <div class="card sf-report-card">
        <div class="card-body">
            @if($rows->count() === 0)
                <p class="text-muted fst-italic">
                    No utilization data found for the selected period.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Branch</th>
                                <th class="text-end">Trips</th>
                                <th class="text-end">Total driving time</th>
                                <th class="text-end">Available time</th>
                                <th>Utilization</th>
                                <th class="text-end">Last used</th>
                                <th>Status</th>
                                <th>Recommendation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $statusLabel = $row->utilization_percent < 20
                                        ? 'Low'
                                        : ($row->utilization_percent >= 85 ? 'Overused' : 'Healthy');
                                    $badgeClass = $row->utilization_percent < 20
                                        ? 'bg-secondary'
                                        : ($row->utilization_percent >= 85 ? 'bg-danger' : 'bg-success');
                                    $recommendation = $row->utilization_percent < 20
                                        ? 'Consider reassigning or reducing idle time'
                                        : ($row->utilization_percent >= 85 ? 'Review load or allocate more vehicles' : 'Healthy usage');
                                @endphp
                                <tr>
                                    <td class="fw-bold">
                                        {{ $row->vehicle_name }}<br>
                                        <small class="text-muted">{{ $row->registration_number }}</small>
                                    </td>
                                    <td>{{ $row->branch_name }}</td>
                                    <td class="text-end">{{ $row->trip_count }}</td>
                                    <td class="text-end">{{ $row->total_duration }}</td>
                                    <td class="text-end">{{ number_format($row->available_hours, 1) }} h</td>
                                    <td>
                                        <div class="sf-util-row">
                                            <div class="sf-util-bar">
                                                <span style="width: {{ $row->utilization_percent }}%"></span>
                                            </div>
                                            <div class="sf-util-label">{{ number_format($row->utilization_percent, 1) }}%</div>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ $row->last_used_at ?: '—' }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>{{ $recommendation }}</td>
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

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/plugins/monthSelect/style.css">

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
    .flatpickr-input.form-control,
    .sf-time.form-control {
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
    .flatpickr-input.form-control:hover,
    .sf-time.form-control:hover {
        background-color: #ffffff;
        border-color: #2CBFAE;
    }

    .sf-date.form-control:focus,
    .flatpickr-input.form-control:focus,
    .sf-time.form-control:focus {
        outline: none;
        background-color: #ffffff;
        border-color: #2CBFAE;
        box-shadow:
            0 0 0 3px rgba(44, 191, 174, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .sf-period-toggle {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .sf-period-toggle label {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(10, 42, 77, 0.08);
        font-weight: 600;
        color: #0A2A4D;
        cursor: pointer;
    }

    .sf-period-toggle input:checked + span {
        color: #0A2A4D;
    }

    .sf-period-toggle input {
        accent-color: #2CBFAE;
    }

    .sf-period-row {
        align-items: flex-start;
    }

    .sf-date-col {
        max-width: 360px;
    }

    .sf-availability-col {
        padding-top: 0;
    }

    .sf-apply-row {
        display: flex;
        align-items: center;
        gap: 12px;
        justify-content: flex-start;
    }

    .sf-advanced-wrap {
        display: flex;
        align-items: flex-end;
        justify-content: flex-end;
    }

    .sf-advanced-panel {
        border-top: 1px dashed rgba(10, 42, 77, 0.15);
        padding-top: 14px;
        display: none;
    }

    .sf-advanced-panel.is-open {
        display: block;
    }

    .sf-days {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .sf-check {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 8px;
        background: rgba(10, 42, 77, 0.08);
        font-weight: 600;
        color: #0A2A4D;
    }

    .sf-time-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }

    .sf-util-row {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }

    .sf-util-bar {
        flex: 1 1 auto;
        height: 8px;
        background: rgba(10, 42, 77, 0.12);
        border-radius: 999px;
        overflow: hidden;
    }

    .sf-util-bar span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #2CBFAE 0%, #0A2A4D 100%);
        border-radius: 999px;
    }

    .sf-util-label {
        width: 54px;
        text-align: right;
        font-weight: 600;
        color: #0A2A4D;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/flatpickr"></script>
<script src="https://unpkg.com/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="/app/sharpfleet/admin/reports/utilization"]');
        const scopeRadios = document.querySelectorAll('input[name="scope"]');
        const branchSelect = document.querySelector('select[name="branch_id"]');
        const vehicleSelect = document.querySelector('select[name="vehicle_id"]');
        const periodRadios = document.querySelectorAll('input[name="period"]');
        const periodLabel = document.getElementById('sfPeriodLabel');
        const advancedToggle = document.getElementById('sfAdvancedToggle');
        const advancedPanel = document.getElementById('sfAdvancedPanel');
        const availabilityPreset = document.getElementById('sfAvailabilityPreset');
        const availabilitySummary = document.getElementById('sfAvailabilitySummary');
        const workStart = document.querySelector('input[name="work_start"]');
        const workEnd = document.querySelector('input[name="work_end"]');
        const dayInputs = document.querySelectorAll('input[name="availability_days[]"]');
        const dateInput = document.querySelector('input[name="period_date"]');
        let periodPicker = null;

        function submitForm() {
            if (!form) return;
            form.submit();
        }

        function updateBranchState(value) {
            if (!branchSelect) return;
            if (value === 'branch') {
                branchSelect.disabled = false;
            } else {
                branchSelect.value = '';
                branchSelect.disabled = true;
            }
        }

        function selectedPeriod() {
            const checked = document.querySelector('input[name="period"]:checked');
            return checked ? checked.value : 'month';
        }

        function updatePeriodLabel() {
            if (!periodLabel) return;
            const period = selectedPeriod();
            periodLabel.textContent = period === 'day'
                ? 'Select day'
                : (period === 'week' ? 'Select week' : 'Select month');
        }

        function updateAvailabilitySummary() {
            if (!availabilitySummary) return;
            const preset = availabilityPreset ? availabilityPreset.value : 'business_hours';
            if (preset === '24_7') {
                availabilitySummary.textContent = 'All days, 24 hours';
                return;
            }
            if (preset === 'business_hours') {
                availabilitySummary.textContent = '7:00am – 5:00pm, Monday to Friday';
                return;
            }
            const days = Array.from(dayInputs)
                .filter(input => input.checked)
                .map(input => input.nextElementSibling ? input.nextElementSibling.textContent.trim() : '')
                .filter(Boolean);
            const dayLabel = days.length ? days.join(', ') : 'Mon-Fri';
            availabilitySummary.textContent = `${workStart.value} - ${workEnd.value}, ${dayLabel}`;
        }

        if (scopeRadios.length) {
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

        if (vehicleSelect) {
            vehicleSelect.addEventListener('change', submitForm);
        }

        if (periodRadios.length) {
            periodRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    updatePeriodLabel();
                    initPeriodPicker();
                    submitForm();
                });
            });
        }

        if (advancedToggle && advancedPanel) {
            advancedToggle.addEventListener('click', function () {
                advancedPanel.classList.toggle('is-open');
            });
        }

        if (availabilityPreset) {
            availabilityPreset.addEventListener('change', function () {
                if (advancedPanel) {
                    advancedPanel.classList.toggle('is-open', availabilityPreset.value === 'custom');
                }
                updateAvailabilitySummary();
                submitForm();
            });
        }

        if (workStart) workStart.addEventListener('change', updateAvailabilitySummary);
        if (workEnd) workEnd.addEventListener('change', updateAvailabilitySummary);
        dayInputs.forEach(function (input) {
            input.addEventListener('change', updateAvailabilitySummary);
        });

        function initPeriodPicker() {
            if (!dateInput || typeof flatpickr === 'undefined') return;

            if (periodPicker) {
                periodPicker.destroy();
                periodPicker = null;
            }

            const period = selectedPeriod();
            const baseOptions = {
                allowInput: true,
                defaultDate: dateInput.value || null,
                onClose: function () {
                    submitForm();
                }
            };

            if (period === 'month' && typeof monthSelectPlugin !== 'undefined') {
                periodPicker = flatpickr(dateInput, Object.assign({}, baseOptions, {
                    plugins: [new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y'
                    })]
                }));
                return;
            }

            const weekOptions = period === 'week'
                ? {
                    altInput: true,
                    altFormat: '\\W\\e\\e\\k\\ \\o\\f {{ $dateFormat }}',
                    dateFormat: 'Y-m-d',
                    onChange: function (selectedDates) {
                        if (!selectedDates || !selectedDates.length) return;
                        const picked = selectedDates[0];
                        const day = picked.getDay(); // 0 Sun - 6 Sat
                        const diff = (day === 0 ? -6 : 1 - day);
                        const weekStart = new Date(picked);
                        weekStart.setDate(picked.getDate() + diff);
                        periodPicker.setDate(weekStart, false);
                    }
                }
                : {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: '{{ $dateFormat }}'
                };

            periodPicker = flatpickr(dateInput, Object.assign({}, baseOptions, weekOptions));
        }

        const initialScope = document.querySelector('input[name="scope"]:checked');
        updateBranchState(initialScope ? initialScope.value : '{{ $uiScope }}');
        initPeriodPicker();
        updatePeriodLabel();
        updateAvailabilitySummary();
    });
</script>
@endpush
