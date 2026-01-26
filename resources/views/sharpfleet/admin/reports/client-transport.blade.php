@extends('layouts.sharpfleet')

@section('title', 'Client Transport Report')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $appTimezone = (string) (config('app.timezone') ?: 'UTC');
    $branches = $branches ?? collect();
    $customers = $customers ?? collect();
    $hasBranches = $branches->count() > 0;
    $showCustomerFilter = $ui['show_customer_filter'] ?? ($customerLinkingEnabled ?? false);
    $controls = $ui['controls_enabled'] ?? [];
    $allowBranchOverride = (bool) ($controls['branch'] ?? true);
    $allowCustomerOverride = (bool) ($controls['customer'] ?? true);
    $allowDateOverride = (bool) ($controls['date'] ?? true);

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

    $selectedCustomerName = null;
    if ($uiCustomerId) {
        $selectedCustomer = $customers->firstWhere('id', (int) $uiCustomerId);
        $selectedCustomerName = $selectedCustomer ? $selectedCustomer->name : null;
    }
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
                <div class="grid grid-4 align-end">
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

                    <div>
                        <label class="form-label">Branch</label>
                        <div class="sf-report-select">
                            <select name="branch_id"
                                    class="form-select"
                                    {{ ($uiScope !== 'branch' || !$hasBranches || !$allowBranchOverride) ? 'disabled' : '' }}>
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

                    <div>
                        <label class="form-label">{{ $clientLabel }}</label>
                        <div
                            x-data="clientSearch({{ $uiCustomerId ? (int) $uiCustomerId : 'null' }}, @js($customers->map(fn ($c) => ['id' => (int) $c->id, 'name' => (string) $c->name])->values()), {{ ($showCustomerFilter && $allowCustomerOverride) ? 'true' : 'false' }})"
                            x-init="init()"
                            class="sf-search"
                        >
                            <input
                                type="text"
                                class="form-control sf-report-input"
                                placeholder="Search {{ $clientLabel }}"
                                x-model="query"
                                x-on:focus="isEnabled ? open = true : null"
                                x-on:input="if (isEnabled) { selectedId = ''; open = true; }"
                                :disabled="!isEnabled"
                                autocomplete="off"
                            >
                            <input type="hidden" name="customer_id" x-model="selectedId">
                            <button
                                type="button"
                                class="sf-search__clear"
                                x-show="isEnabled && query"
                                x-on:click="clear()"
                                aria-label="Clear selection"
                            >
                                ×
                            </button>
                            <div
                                class="sf-search__panel"
                                x-show="open && isEnabled"
                                x-on:click.outside="open = false"
                            >
                                <template x-if="filtered.length === 0">
                                    <div class="sf-search__empty">No matches found.</div>
                                </template>
                                <template x-for="item in filtered" :key="item.id">
                                    <button
                                        type="button"
                                        class="sf-search__option"
                                        x-text="item.name"
                                        x-on:click="select(item)"
                                    ></button>
                                </template>
                            </div>
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

    .sf-report-input.form-control {
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
    }

    .sf-report-input.form-control:hover {
        background-color: #ffffff;
        border-color: #2CBFAE;
    }

    .sf-report-input.form-control:focus {
        outline: none;
        background-color: #ffffff;
        border-color: #2CBFAE;
        box-shadow:
            0 0 0 3px rgba(44, 191, 174, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .sf-report-input.form-control:disabled {
        background-color: #eef2f6;
        color: rgba(10, 42, 77, 0.5);
        border-color: rgba(10, 42, 77, 0.15);
        cursor: not-allowed;
    }

    .sf-search {
        position: relative;
    }

    .sf-search__panel {
        position: absolute;
        z-index: 20;
        left: 0;
        right: 0;
        margin-top: 6px;
        background: #ffffff;
        border: 1px solid rgba(44, 191, 174, 0.25);
        border-radius: 12px;
        box-shadow: 0 12px 24px rgba(10, 42, 77, 0.12);
        max-height: 240px;
        overflow: auto;
        padding: 6px;
    }

    .sf-search__option {
        width: 100%;
        text-align: left;
        border: none;
        background: transparent;
        padding: 8px 10px;
        border-radius: 8px;
        font-weight: 600;
        color: #0A2A4D;
        cursor: pointer;
        transition: background-color 120ms ease, color 120ms ease;
    }

    .sf-search__option:hover,
    .sf-search__option:focus {
        background: rgba(44, 191, 174, 0.12);
        color: #0A2A4D;
        outline: none;
    }

    .sf-search__empty {
        padding: 10px;
        color: #6b7a90;
        font-size: 0.9rem;
    }

    .sf-search__clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #6b7a90;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
    }

    .sf-radio-row {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .sf-radio {
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

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/flatpickr"></script>
<script>
    function clientSearch(initialId, items, isEnabled) {
        return {
            open: false,
            query: '',
            selectedId: initialId || '',
            items: Array.isArray(items) ? items : [],
            isEnabled: !!isEnabled,
            init() {
                if (this.selectedId) {
                    const selected = this.items.find(item => String(item.id) === String(this.selectedId));
                    if (selected) this.query = selected.name;
                }
            },
            get filtered() {
                const q = (this.query || '').toLowerCase();
                if (!q) return this.items;
                return this.items.filter(item => String(item.name).toLowerCase().includes(q));
            },
            select(item) {
                this.selectedId = item.id;
                this.query = item.name;
                this.open = false;
            },
            clear() {
                this.selectedId = '';
                this.query = '';
                this.open = false;
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="/app/sharpfleet/admin/reports/client-transport"]');
        const scopeRadios = document.querySelectorAll('input[name="scope"]');
        const branchSelect = document.querySelector('select[name="branch_id"]');
        const branchIdsHidden = document.querySelector('input[name="branch_ids[]"]');

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

        document.addEventListener('change', function (event) {
            if (event.target && event.target.matches('[name="customer_id"]')) {
                submitForm();
            }
        });

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

        if (form) {
            form.addEventListener('submit', function () {
                syncBranchIds();
            });
        }

        const initialScope = document.querySelector('input[name="scope"]:checked');
        updateBranchState(initialScope ? initialScope.value : '{{ $uiScope }}');
        syncBranchIds();
    });
</script>
@endpush
