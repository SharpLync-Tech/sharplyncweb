@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Bookings')

@section('content')
@php
    use Carbon\Carbon;
    $dayStartUtc = $dayStartLocal->copy()->timezone('UTC');
    $dayEndUtc = $dayEndLocal->copy()->timezone('UTC');
    $weekStartUtc = $weekStartLocal->copy()->timezone('UTC');
    $weekEndUtc = $weekEndLocal->copy()->timezone('UTC');
    $monthStartUtc = $monthStartLocal->copy()->timezone('UTC');
    $monthEndUtc = $monthEndLocal->copy()->timezone('UTC');

    $filterRange = function ($rows, $startUtc, $endUtc) {
        return $rows->filter(function ($b) use ($startUtc, $endUtc) {
            $start = Carbon::parse($b->planned_start)->utc();
            $end = Carbon::parse($b->planned_end)->utc();
            return $start->lessThanOrEqualTo($endUtc) && $end->greaterThanOrEqualTo($startUtc);
        })->values();
    };

    $bookingsMineDay = $filterRange($bookingsMine, $dayStartUtc, $dayEndUtc);
    $bookingsOtherDay = $filterRange($bookingsOther, $dayStartUtc, $dayEndUtc);
    $bookingsMineWeek = $filterRange($bookingsMine, $weekStartUtc, $weekEndUtc);
    $bookingsOtherWeek = $filterRange($bookingsOther, $weekStartUtc, $weekEndUtc);
    $bookingsMineMonth = $filterRange($bookingsMine, $monthStartUtc, $monthEndUtc);
    $bookingsOtherMonth = $filterRange($bookingsOther, $monthStartUtc, $monthEndUtc);
@endphp

<section class="sf-mobile-dashboard">
    <h1 class="sf-mobile-title">Bookings</h1>
    <p class="sf-mobile-subtitle">Manage your bookings and see vehicle availability.</p>

    <div class="sf-mobile-card" style="margin-bottom: 16px;">
        <div class="sf-mobile-card-title">Bookings</div>
        <div class="sf-mobile-card-text">Open a range view or create a booking.</div>

        <div style="display: flex; gap: 8px; margin-top: 10px;">
            <button type="button" class="sf-mobile-secondary-btn" data-sheet-open="bookings-day" style="flex:1; padding: 12px;">Day</button>
            <button type="button" class="sf-mobile-secondary-btn" data-sheet-open="bookings-week" style="flex:1; padding: 12px;">Week</button>
            <button type="button" class="sf-mobile-secondary-btn" data-sheet-open="bookings-month" style="flex:1; padding: 12px;">Month</button>
        </div>

        <button type="button" class="sf-mobile-primary-btn" data-sheet-open="booking-create" style="margin-top: 12px;">
            Create Booking
        </button>
    </div>
</section>

{{-- Day Sheet --}}
<div id="sf-sheet-bookings-day" class="sf-sheet" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="sf-bookings-day-title">
    <div class="sf-sheet-header">
        <h2 id="sf-bookings-day-title">Day Bookings</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close aria-label="Close">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>
    <div class="sf-sheet-body">
        @php
            $mineGrouped = $bookingsMineDay->groupBy(function ($b) {
                return \Carbon\Carbon::parse($b->planned_start)->utc()->toDateString();
            });
            $otherGrouped = $bookingsOtherDay->groupBy(function ($b) {
                return \Carbon\Carbon::parse($b->planned_start)->utc()->toDateString();
            });
        @endphp

        <div class="sf-mobile-card" style="margin-bottom: 12px;">
            <div class="sf-mobile-card-title">My Bookings</div>
            @if(!$bookingsTableExists)
                <div class="hint-text">Bookings are unavailable until the database table is created.</div>
            @elseif($bookingsMineDay->count() === 0)
                <div class="hint-text">No bookings in this range.</div>
            @else
                @foreach($mineGrouped as $date => $rows)
                    <div style="margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                        <div class="hint-text"><strong>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</strong></div>
                    @foreach($rows as $b)
                            @php
                                $rowTz = isset($b->timezone) && trim((string) $b->timezone) !== '' ? (string) $b->timezone : $companyTimezone;
                                $startLocal = Carbon::parse($b->planned_start)->utc()->timezone($rowTz)->format('g:i A');
                                $endLocal = Carbon::parse($b->planned_end)->utc()->timezone($rowTz)->format('g:i A');
                                $endUtc = Carbon::parse($b->planned_end)->utc();
                                $canCancel = $endUtc->greaterThan($nowLocal->copy()->timezone('UTC'));
                            @endphp
                            <div class="hint-text" style="margin-top: 6px;">
                                <strong>{{ $b->vehicle_name }}</strong> ({{ $b->registration_number }}) · {{ $startLocal }} - {{ $endLocal }}
                            </div>
                            @if(!empty($b->customer_name_display))
                                <div class="hint-text" style="margin-top: 4px;"><strong>Customer:</strong> {{ $b->customer_name_display }}</div>
                            @endif
                            @if($canCancel)
                                <form method="POST" action="{{ url('/app/sharpfleet/bookings/' . (int) $b->id . '/cancel') }}" style="margin-top: 6px;">
                                    @csrf
                                    <button type="submit" class="sf-mobile-secondary-btn" style="padding: 10px;">Cancel Booking</button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>

        <div class="sf-mobile-card">
            <div class="sf-mobile-card-title">Other Booked Vehicles</div>
            @if(!$bookingsTableExists)
                <div class="hint-text">Bookings are unavailable until the database table is created.</div>
            @elseif($bookingsOtherDay->count() === 0)
                <div class="hint-text">No other bookings in this range.</div>
            @else
                @foreach($otherGrouped as $date => $rows)
                    <div style="margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                        <div class="hint-text"><strong>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</strong></div>
                        @foreach($rows as $b)
                            @php
                                $rowTz = isset($b->timezone) && trim((string) $b->timezone) !== '' ? (string) $b->timezone : $companyTimezone;
                                $startLocal = Carbon::parse($b->planned_start)->utc()->timezone($rowTz)->format('g:i A');
                                $endLocal = Carbon::parse($b->planned_end)->utc()->timezone($rowTz)->format('g:i A');
                            @endphp
                            <div class="hint-text" style="margin-top: 6px;">
                                <strong>{{ $b->vehicle_name }}</strong> ({{ $b->registration_number }}) · {{ $startLocal }} - {{ $endLocal }}
                            </div>
                            <div class="hint-text" style="margin-top: 4px;">Booked</div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

{{-- Week Sheet --}}
<div id="sf-sheet-bookings-week" class="sf-sheet" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="sf-bookings-week-title">
    <div class="sf-sheet-header">
        <h2 id="sf-bookings-week-title">Week Bookings</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close aria-label="Close">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>
    <div class="sf-sheet-body">
        <div class="hint-text" style="margin-bottom: 12px;">
            {{ $weekStartLocal->format('M j') }} - {{ $weekEndLocal->format('M j') }}
        </div>
        @include('sharpfleet.mobile.partials.bookings-calendar', [
            'bookingsMine' => $bookingsMineWeek,
            'bookingsOther' => $bookingsOtherWeek,
        ])
    </div>
</div>

{{-- Month Sheet --}}
<div id="sf-sheet-bookings-month" class="sf-sheet" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="sf-bookings-month-title">
    <div class="sf-sheet-header">
        <h2 id="sf-bookings-month-title">Month Bookings</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close aria-label="Close">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>
    <div class="sf-sheet-body">
        <div class="hint-text" style="margin-bottom: 12px;">
            {{ $monthStartLocal->format('M j') }} - {{ $monthEndLocal->format('M j') }}
        </div>
        @include('sharpfleet.mobile.partials.bookings-calendar', [
            'bookingsMine' => $bookingsMineMonth,
            'bookingsOther' => $bookingsOtherMonth,
        ])
    </div>
</div>

{{-- Create Booking Sheet --}}
<div id="sf-sheet-booking-create" class="sf-sheet" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="sf-booking-create-title">
    <div class="sf-sheet-header">
        <h2 id="sf-booking-create-title">Create Booking</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close aria-label="Close">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>
    <div class="sf-sheet-body">
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
</div>

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
