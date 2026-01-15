@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Bookings')

@section('content')
@php
    use Carbon\Carbon;
    $rangeLabel = ucfirst($range ?? 'week');
@endphp

<section class="sf-mobile-dashboard">
    <h1 class="sf-mobile-title">Bookings</h1>
    <p class="sf-mobile-subtitle">Manage your bookings and see vehicle availability.</p>

    <div class="sf-mobile-card" style="margin-bottom: 16px;">
        <div class="sf-mobile-card-title">View Range</div>
        <div style="display: flex; gap: 8px; margin-top: 10px;">
            @foreach (['day' => 'Day', 'week' => 'Week', 'month' => 'Month'] as $key => $label)
                @php $active = ($range ?? 'week') === $key; @endphp
                <a
                    href="{{ url('/app/sharpfleet/mobile/bookings') }}?range={{ $key }}"
                    class="sf-mobile-secondary-btn"
                    style="flex: 1; padding: 12px; text-align: center; {{ $active ? 'border:1px solid rgba(44,191,174,0.7); color:#2CBFAE;' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <div class="hint-text" style="margin-top: 10px;">
            Showing {{ $rangeLabel }} bookings ({{ $rangeStartLocal->format('M j') }} - {{ $rangeEndLocal->format('M j') }})
        </div>
    </div>

    <div class="sf-mobile-card" style="margin-bottom: 16px;">
        <div class="sf-mobile-card-title">New Booking</div>
        <div class="sf-mobile-card-text">Bookings require an internet connection.</div>

        @if(!$bookingsTableExists)
            <div class="hint-text">Bookings are unavailable until the database table is created.</div>
        @else
            <form method="POST" action="/app/sharpfleet/bookings">
                @csrf

                @if($branchesEnabled && $branches->count() > 1)
                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        <select id="mobileBookingBranchSelect" name="branch_id" class="form-control" required>
                            <option value="">- Select branch -</option>
                            @foreach($branches as $br)
                                <option value="{{ $br->id }}">{{ $br->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($branchesEnabled && $branches->count() === 1)
                    <input type="hidden" id="mobileBookingBranchSelect" name="branch_id" value="{{ (int) ($branches->first()->id ?? 0) }}">
                @endif

                <div class="grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label class="form-label">Start date</label>
                        <input type="date" name="planned_start_date" class="form-control" required min="{{ $today }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start time</label>
                        <input type="time" name="planned_start_time" class="form-control" required step="60">
                    </div>
                </div>

                <div class="grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label class="form-label">End date</label>
                        <input type="date" name="planned_end_date" class="form-control" required min="{{ $today }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End time</label>
                        <input type="time" name="planned_end_time" class="form-control" required step="60">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Vehicle (available only)</label>
                    <div id="mobileBookingVehicleStatus" class="hint-text">Select start/end date & time to load available vehicles.</div>
                    <select id="mobileBookingVehicleSelect" name="vehicle_id" class="form-control" required disabled>
                        <option value="">- Select vehicle -</option>
                    </select>
                </div>

                @if($customersTableExists && $customers->count() > 0)
                    <div class="form-group">
                        <label class="form-label">Customer / Client (optional)</label>
                        <select id="mobileBookingCustomerSelect" name="customer_id" class="form-control">
                            <option value="">- Select from list -</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="hint-text">If the customer isn't in the list, type a name below.</div>
                        <input id="mobileBookingCustomerNameInput" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter customer name">
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                </div>

                <button id="mobileBookingSubmit" type="submit" class="sf-mobile-primary-btn" style="margin-top: 10px;" disabled>
                    Create Booking
                </button>
            </form>
        @endif
    </div>

    <div class="sf-mobile-card" style="margin-bottom: 16px;">
        <div class="sf-mobile-card-title">My Bookings</div>

        @if(!$bookingsTableExists)
            <div class="hint-text">Bookings are unavailable until the database table is created.</div>
        @elseif($bookingsMine->count() === 0)
            <div class="hint-text">No bookings in this range.</div>
        @else
            @foreach($bookingsMine as $b)
                @php
                    $rowTz = isset($b->timezone) && trim((string) $b->timezone) !== '' ? (string) $b->timezone : $companyTimezone;
                    $startLocal = Carbon::parse($b->planned_start)->utc()->timezone($rowTz)->format('M j, g:i A');
                    $endLocal = Carbon::parse($b->planned_end)->utc()->timezone($rowTz)->format('M j, g:i A');
                @endphp
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.08);">
                    <div class="hint-text"><strong>Vehicle:</strong> {{ $b->vehicle_name }} ({{ $b->registration_number }})</div>
                    <div class="hint-text" style="margin-top: 4px;"><strong>Time:</strong> {{ $startLocal }} - {{ $endLocal }}</div>
                    @if(!empty($b->customer_name_display))
                        <div class="hint-text" style="margin-top: 4px;"><strong>Customer:</strong> {{ $b->customer_name_display }}</div>
                    @endif
                    <form method="POST" action="{{ url('/app/sharpfleet/bookings/' . (int) $b->id . '/cancel') }}" style="margin-top: 8px;">
                        @csrf
                        <button type="submit" class="sf-mobile-secondary-btn" style="padding: 10px;">Cancel Booking</button>
                    </form>
                </div>
            @endforeach
        @endif
    </div>

    <div class="sf-mobile-card">
        <div class="sf-mobile-card-title">Other Booked Vehicles</div>

        @if(!$bookingsTableExists)
            <div class="hint-text">Bookings are unavailable until the database table is created.</div>
        @elseif($bookingsOther->count() === 0)
            <div class="hint-text">No other bookings in this range.</div>
        @else
            @foreach($bookingsOther as $b)
                @php
                    $rowTz = isset($b->timezone) && trim((string) $b->timezone) !== '' ? (string) $b->timezone : $companyTimezone;
                    $startLocal = Carbon::parse($b->planned_start)->utc()->timezone($rowTz)->format('M j, g:i A');
                    $endLocal = Carbon::parse($b->planned_end)->utc()->timezone($rowTz)->format('M j, g:i A');
                @endphp
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.08);">
                    <div class="hint-text"><strong>Vehicle:</strong> {{ $b->vehicle_name }} ({{ $b->registration_number }})</div>
                    <div class="hint-text" style="margin-top: 4px;"><strong>Time:</strong> {{ $startLocal }} - {{ $endLocal }}</div>
                    <div class="hint-text" style="margin-top: 4px;">Booked</div>
                </div>
            @endforeach
        @endif
    </div>
</section>

<script>
    (function () {
        const startDate = document.querySelector('input[name="planned_start_date"]');
        const startTime = document.querySelector('input[name="planned_start_time"]');
        const endDate = document.querySelector('input[name="planned_end_date"]');
        const endTime = document.querySelector('input[name="planned_end_time"]');
        const branchSelect = document.getElementById('mobileBookingBranchSelect');

        const vehicleSelect = document.getElementById('mobileBookingVehicleSelect');
        const vehicleStatus = document.getElementById('mobileBookingVehicleStatus');
        const submitBtn = document.getElementById('mobileBookingSubmit');

        function updateSubmitState() {
            if (!submitBtn || !vehicleSelect) return;
            submitBtn.disabled = !vehicleSelect.value;
        }

        function setVehicleOptions(vehicles) {
            if (!vehicleSelect) return;
            vehicleSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = vehicles.length ? '- Select vehicle -' : 'No vehicles available';
            vehicleSelect.appendChild(placeholder);
            vehicles.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = `${v.name} (${v.registration_number})`;
                vehicleSelect.appendChild(opt);
            });
        }

        async function loadVehicles() {
            if (!vehicleSelect || !startDate || !startTime || !endDate || !endTime) return;

            if (!startDate.value || !startTime.value || !endDate.value || !endTime.value) {
                vehicleSelect.disabled = true;
                setVehicleOptions([]);
                if (vehicleStatus) vehicleStatus.textContent = 'Select start/end date & time to load available vehicles.';
                updateSubmitState();
                return;
            }

            const params = new URLSearchParams({
                planned_start_date: startDate.value,
                planned_start_time: startTime.value,
                planned_end_date: endDate.value,
                planned_end_time: endTime.value,
            });

            if (branchSelect && branchSelect.value) {
                params.set('branch_id', branchSelect.value);
            }

            vehicleSelect.disabled = true;
            if (vehicleStatus) vehicleStatus.textContent = 'Loading available vehicles...';

            try {
                const res = await fetch(`/app/sharpfleet/bookings/available-vehicles?${params.toString()}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });

                if (!res.ok) {
                    setVehicleOptions([]);
                    vehicleSelect.disabled = true;
                    if (vehicleStatus) vehicleStatus.textContent = 'Could not load vehicles for that time window.';
                    updateSubmitState();
                    return;
                }

                const data = await res.json();
                const vehicles = Array.isArray(data.vehicles) ? data.vehicles : [];
                setVehicleOptions(vehicles);
                vehicleSelect.disabled = false;
                if (vehicleStatus) {
                    vehicleStatus.textContent = vehicles.length
                        ? `Available vehicles: ${vehicles.length}`
                        : 'No vehicles available for this time window.';
                }
                updateSubmitState();
            } catch (e) {
                setVehicleOptions([]);
                vehicleSelect.disabled = true;
                if (vehicleStatus) vehicleStatus.textContent = 'Could not load vehicles (network error).';
                updateSubmitState();
            }
        }

        if (vehicleSelect) {
            vehicleSelect.addEventListener('change', updateSubmitState);
        }

        [startDate, startTime, endDate, endTime].forEach(el => {
            if (el) el.addEventListener('change', loadVehicles);
        });

        if (branchSelect) {
            branchSelect.addEventListener('change', loadVehicles);
        }
    })();
</script>
@endsection
