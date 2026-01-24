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
