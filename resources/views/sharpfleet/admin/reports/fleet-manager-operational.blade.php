@extends('layouts.sharpfleet')

@section('title', 'Fleet Manager Reports (Operational)')

@section('sharpfleet-content')

@php
    $dateFormat = $dateFormat ?? (str_starts_with(($companyTimezone ?? ''), 'America/') ? 'm/d/Y' : 'd/m/Y');
    $uiStartDate = $startDate ?? request('start_date');
    $uiEndDate = $endDate ?? request('end_date');
    $uiStatus = $statusFilter ?? request('status', 'all');
    $uiBranchId = $branchId ?? request('branch_id');
    $hasBranches = isset($branches) && $branches->count() > 1;
    $datePlaceholder = $dateFormat === 'm/d/Y' ? 'mm/dd/yyyy' : 'dd/mm/yyyy';
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Fleet Manager Reports (Operational)</h1>
                <p class="page-description">
                    Daily/weekly operational view of fleet usage, idle vehicles, and last active dates.
                </p>
            </div>

            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/fleet-manager-operational') }}">
                <input type="hidden" name="export" value="csv">
                <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                <input type="hidden" name="status" value="{{ $uiStatus }}">
                <input type="hidden" name="branch_id" value="{{ $uiBranchId }}">
                <button type="submit" class="btn btn-primary">
                    Export Report (CSV)
                </button>
            </form>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <form method="GET"
          action="{{ url('/app/sharpfleet/admin/reports/fleet-manager-operational') }}"
          class="card mb-3" id="fleetManagerReportFilters">
        <div class="card-body">
            <div class="grid grid-4 align-end">

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

                <div>
                    <label class="form-label">Status</label>
                    <div class="sf-report-select">
                        <select name="status" class="form-select" data-auto-submit="1">
                            <option value="all" {{ $uiStatus === 'all' ? 'selected' : '' }}>All vehicles</option>
                            <option value="active" {{ $uiStatus === 'active' ? 'selected' : '' }}>Active only</option>
                            <option value="inactive" {{ $uiStatus === 'inactive' ? 'selected' : '' }}>Inactive only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Branch</label>
                    <div class="sf-report-select">
                        <select name="branch_id" class="form-select" data-auto-submit="1" {{ !$hasBranches ? 'disabled' : '' }}>
                            <option value="">All branches</option>
                            @foreach($branches ?? [] as $b)
                                <option value="{{ $b->id }}" {{ (string) $uiBranchId === (string) $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-outline-primary">
                    Update Report
                </button>
            </div>

            <div class="text-muted small mt-2">
                Default range is the most recent 7 days when dates are not specified.
                Layout is designed for wide tables and future PDF support.
            </div>
        </div>
    </form>

    @push('styles')
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">

<style>
    /* =====================================================
       Global reset for selects (kill native arrows)
    ===================================================== */
    select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none;
    }

    select::-ms-expand {
        display: none;
    }

    /* =====================================================
       SharpFleet SELECTS (Status / Branch)
    ===================================================== */
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

    /* =====================================================
       SharpFleet DATE INPUTS (Flatpickr)
       Real inputs are type="text"
    ===================================================== */
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
    .sf-date.form-control.active,
    .flatpickr-input.form-control:focus,
    .flatpickr-input.form-control.active {
        outline: none;
        background-color: #ffffff;
        border-color: #2CBFAE;
        box-shadow:
            0 0 0 3px rgba(44, 191, 174, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .sf-date::placeholder {
        color: rgba(10, 42, 77, 0.45);
        font-weight: 500;
    }
</style>
@endpush


    @push('scripts')
    <script src="https://unpkg.com/flatpickr"></script>
    <script>
        (function () {
            if (typeof flatpickr !== 'undefined') {
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
            }

            const form = document.getElementById('fleetManagerReportFilters');
            if (!form) return;
            form.querySelectorAll('select[data-auto-submit="1"]').forEach((select) => {
                select.addEventListener('change', () => form.submit());
            });
        })();
    </script>
    @endpush

    {{-- ================= RESULTS ================= --}}
    <div class="card">
        <div class="card-body">

            @if(($rows ?? collect())->count() === 0)
                <p class="text-muted fst-italic">
                    No vehicles found for the selected filters.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Branch</th>
                                <th class="text-end">Trips</th>
                                <th class="text-end">Total distance</th>
                                <th class="text-end">Total duration</th>
                                <th class="text-end">Avg / trip</th>
                                <th>Last used</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row->vehicle_name }}</td>
                                    <td>{{ $row->branch_name ?: '—' }}</td>
                                    <td class="text-end">{{ $row->trip_count }}</td>
                                    <td class="text-end">{{ $row->total_distance_label }}</td>
                                    <td class="text-end">{{ $row->total_duration }}</td>
                                    <td class="text-end">{{ $row->average_distance_label }}</td>
                                    <td>{{ $row->last_used_at ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $row->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $row->status }}
                                        </span>
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
