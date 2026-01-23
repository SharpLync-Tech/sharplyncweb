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
          class="card mb-3">
        <div class="card-body">
            <div class="grid grid-4 align-end">

                <div>
                    <label class="form-label">Start date</label>
                    <input type="date"
                           name="start_date"
                           class="form-control"
                           value="{{ $uiStartDate }}">
                </div>

                <div>
                    <label class="form-label">End date</label>
                    <input type="date"
                           name="end_date"
                           class="form-control"
                           value="{{ $uiEndDate }}">
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ $uiStatus === 'all' ? 'selected' : '' }}>All vehicles</option>
                        <option value="active" {{ $uiStatus === 'active' ? 'selected' : '' }}>Active only</option>
                        <option value="inactive" {{ $uiStatus === 'inactive' ? 'selected' : '' }}>Inactive only</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select" {{ !$hasBranches ? 'disabled' : '' }}>
                        <option value="">All branches</option>
                        @foreach($branches ?? [] as $b)
                            <option value="{{ $b->id }}" {{ (string) $uiBranchId === (string) $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
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
