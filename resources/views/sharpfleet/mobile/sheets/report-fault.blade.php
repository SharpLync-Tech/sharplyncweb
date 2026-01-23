{{-- =========================================================
     SharpFleet Mobile  Report Vehicle Issue
========================================================= --}}

<div
    id="sf-sheet-report-fault"
    class="sf-sheet"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="sf-report-fault-title"
>

    {{-- ===============================
         Sheet Header
    ================================ --}}
    <div class="sf-sheet-header">
        <h2 id="sf-report-fault-title">Report Vehicle Issue</h2>

        <button
            type="button"
            class="sf-sheet-close"
            data-sheet-close
            aria-label="Close"
        >
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    {{-- ===============================
         Sheet Body
    ================================ --}}
    <div class="sf-sheet-body">
        <form method="POST" action="/app/sharpfleet/faults/standalone" id="reportFaultForm" data-mobile-token-form>
            @csrf

            <div class="form-group">
                <label class="form-label">Vehicle</label>
                <select name="vehicle_id" class="form-control" required>
                    @foreach ($vehicles as $vehicle)
                        @php
                            $selectedVehicleId = (int) ($activeTrip->vehicle_id ?? 0);
                            $vehicleId = (int) ($vehicle->id ?? 0);
                        @endphp
                        <option value="{{ $vehicle->id }}" {{ $vehicleId === $selectedVehicleId ? 'selected' : '' }}>
                            {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="report_type" class="form-control" required>
                    <option value="issue">Vehicle Issue</option>
                    <option value="accident">Vehicle Accident</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Severity</label>
                <select name="severity" class="form-control" required>
                    <option value="minor">Minor</option>
                    <option value="major">Major</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Title (optional)</label>
                <input
                    type="text"
                    name="title"
                    class="form-control"
                    maxlength="150"
                    placeholder="e.g. Service due / Panel damage"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea
                    name="description"
                    class="form-control"
                    rows="4"
                    required
                    placeholder="Describe the fault/incident."
                ></textarea>
            </div>

            <button type="submit" class="sf-mobile-primary-btn" style="margin-top: 12px;">
                Submit Report
            </button>
        </form>
    </div>
</div>


