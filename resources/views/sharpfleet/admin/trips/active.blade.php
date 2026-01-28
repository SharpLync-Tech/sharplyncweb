@extends('layouts.sharpfleet')

@section('title', 'Current Active Trips')

@section('sharpfleet-content')
<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Current Active Trips</h1>
                <p class="page-description">Trips that have started but have not ended yet.</p>
            </div>
            <a href="{{ url('/app/sharpfleet/admin') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if(!($tripsTableExists ?? false))
        <div class="alert alert-warning">
            Trips aren't available yet because the database is missing the <strong>trips</strong> table.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if(($tripsTableExists ?? false) && (!isset($trips) || $trips->count() === 0))
                <p class="text-muted fst-italic">No active trips found.</p>
            @elseif(($tripsTableExists ?? false))
                <div class="row g-2 align-items-end" style="margin-bottom:12px;">
                    <div class="col-md-5">
                        <label for="activeTripsSearch" class="form-label">Search {{ strtolower($clientLabel ?? 'customer') }}</label>
                        <input id="activeTripsSearch" type="text" class="form-control" placeholder="Search by {{ strtolower($clientLabel ?? 'customer') }} name">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check" style="margin-top:32px;">
                            <input class="form-check-input" type="checkbox" id="filterClientPresent">
                            <label class="form-check-label" for="filterClientPresent">{{ $clientLabel ?? 'Customer' }} present only</label>
                        </div>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <span id="activeTripsCount" class="text-muted small"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="activeTripsTable">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Start time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                @php
                                    $vehicleName = (string) ($t->vehicle_name ?? '&mdash;');
                                    $rego = (string) ($t->registration_number ?? '');
                                    if ($rego !== '') {
                                        $vehicleName .= ' (' . $rego . ')';
                                    }

                                    $driverName = trim((string)($t->driver_first_name ?? '') . ' ' . (string)($t->driver_last_name ?? ''));
                                    if ($driverName === '') {
                                        $driverName = ($t->driver_id ?? null) ? ('User #' . (int) $t->driver_id) : '&mdash;';
                                    }

                                    $startedAt = $t->started_at ?? null;
                                    $tripTz = isset($t->timezone) && trim((string) $t->timezone) !== "" ? (string) $t->timezone : ($companyTimezone ?? "UTC");
                                    $startedAtLabel = "&mdash;";
                                    if ($startedAt) {
                                        try {
                                            $startedAtLabel = \Carbon\Carbon::parse($startedAt)->timezone($tripTz)->format("M j, Y g:i A");
                                        } catch (\Throwable $e) {
                                            $startedAtLabel = (string) $startedAt;
                                        }
                                    }

                                    $customerName = trim((string) ($t->customer_name_display ?? $t->customer_name ?? ''));
                                    if ($customerName === '') {
                                        $customerName = '&mdash;';
                                    }
                                    $clientPresent = $t->client_present ?? null;
                                    $clientPresentLabel = $clientPresent === null || $clientPresent === '' ? '&mdash;' : ((int) $clientPresent === 1 ? 'Yes' : 'No');
                                    $clientPresentData = $clientPresent === null || $clientPresent === '' ? 'unknown' : ((int) $clientPresent === 1 ? 'yes' : 'no');
                                @endphp
                                <tr data-customer="{{ strtolower($customerName) }}" data-client-present="{{ $clientPresentData }}">
                                    <td class="fw-bold">{{ $vehicleName ?: '&mdash;' }}</td>
                                    <td>{{ $driverName }}</td>
                                    <td>{{ $startedAtLabel }}</td>
                                    <td>
                                        <details>
                                            <summary>View</summary>
                                            <div class="text-muted" style="margin-top:8px;">
                                                <div><strong>Vehicle:</strong> {{ $vehicleName ?: '&mdash;' }}</div>
                                                <div><strong>Driver:</strong> {{ $driverName }}</div>
                                                <div><strong>{{ $clientLabel ?? 'Customer' }}:</strong> {{ $customerName }}</div>
                                                <div><strong>Started:</strong> {{ $startedAtLabel }}</div>
                                                <div><strong>{{ $clientLabel ?? 'Customer' }} present:</strong> {{ $clientPresentLabel }}</div>
                                            </div>
                                        </details>
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
@if(($tripsTableExists ?? false))
<script>
    (function () {
        var searchInput = document.getElementById("activeTripsSearch");
        var filterClientPresent = document.getElementById("filterClientPresent");
        var countLabel = document.getElementById("activeTripsCount");
        var table = document.getElementById("activeTripsTable");
        if (!searchInput || !filterClientPresent || !table) {
            return;
        }

        var rows = Array.prototype.slice.call(table.querySelectorAll("tbody tr"));

        function applyFilters() {
            var query = (searchInput.value || "").trim().toLowerCase();
            var requireClientPresent = filterClientPresent.checked;
            var visible = 0;

            rows.forEach(function (row) {
                var customer = (row.getAttribute("data-customer") || "").toLowerCase();
                var present = row.getAttribute("data-client-present") || "unknown";
                var matchesQuery = query === "" || customer.indexOf(query) !== -1;
                var matchesPresent = !requireClientPresent || present === "yes";
                var show = matchesQuery && matchesPresent;
                row.style.display = show ? "" : "none";
                if (show) {
                    visible += 1;
                }
            });

            if (countLabel) {
                countLabel.textContent = visible + " visible";
            }
        }

        searchInput.addEventListener("input", applyFilters);
        filterClientPresent.addEventListener("change", applyFilters);
        applyFilters();
    })();
</script>
@endif
@endsection







