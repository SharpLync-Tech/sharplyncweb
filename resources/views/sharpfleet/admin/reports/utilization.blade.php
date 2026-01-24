@extends('layouts.sharpfleet')

@section('title', 'Utilization Report')

@section('sharpfleet-content')

@php
    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $branches = $branches ?? collect();
    $hasBranches = $branches->count() > 1;

    $uiBranchId = request('branch_id');
    $uiStartDate = request('start_date', $startDate ?? '');
    $uiEndDate = request('end_date', $endDate ?? '');

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
            <div class="grid grid-3 align-end">
                <div>
                    <label class="form-label">Branch</label>
                    <div class="sf-report-select">
                        <select name="branch_id" class="form-select" {{ $hasBranches ? '' : 'disabled' }}>
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
                <button type="submit" class="btn-sf-navy">Update Report</button>
            </div>
        </div>
    </form>

    <div class="card sf-report-card mb-3">
        <div class="card-body">
            <div class="grid grid-3 text-center">
                <div>
                    <strong>{{ number_format($averageUtilization, 1) }}%</strong><br>
                    <span class="text-muted small">Average utilization</span>
                </div>
                <div>
                    <strong>{{ $rows->count() }}</strong><br>
                    <span class="text-muted small">Active vehicles</span>
                </div>
                <div>
                    <strong>{{ $startDate }} - {{ $endDate }}</strong><br>
                    <span class="text-muted small">Report range</span>
                </div>
            </div>
        </div>
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
                                <th>Utilization</th>
                                <th class="text-end">Last used</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $row->vehicle_name }}<br>
                                        <small class="text-muted">{{ $row->registration_number }}</small>
                                    </td>
                                    <td>{{ $row->branch_name }}</td>
                                    <td class="text-end">{{ $row->trip_count }}</td>
                                    <td class="text-end">{{ $row->total_duration }}</td>
                                    <td>
                                        <div class="sf-util-row">
                                            <div class="sf-util-bar">
                                                <span style="width: {{ $row->utilization_percent }}%"></span>
                                            </div>
                                            <div class="sf-util-label">{{ number_format($row->utilization_percent, 1) }}%</div>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ $row->last_used_at ?: '—' }}</td>
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

<style>
    .sf-report-card {
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 14px;
        background: #EEF3F8;
        box-shadow: 0 10px 18px rgba(10, 42, 77, 0.16);
    }

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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="/app/sharpfleet/admin/reports/utilization"]');
        const branchSelect = document.querySelector('select[name="branch_id"]');

        function submitForm() {
            if (!form) return;
            form.submit();
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
    });
</script>
@endpush
