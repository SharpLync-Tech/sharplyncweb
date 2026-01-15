@php
    $mineGrouped = $bookingsMine->groupBy(function ($b) {
        return \Carbon\Carbon::parse($b->planned_start)->utc()->toDateString();
    });
    $otherGrouped = $bookingsOther->groupBy(function ($b) {
        return \Carbon\Carbon::parse($b->planned_start)->utc()->toDateString();
    });
@endphp

<div class="sf-mobile-card" style="margin-bottom: 12px;">
    <div class="sf-mobile-card-title">My Bookings</div>
    @if(!$bookingsTableExists)
        <div class="hint-text">Bookings are unavailable until the database table is created.</div>
    @elseif($bookingsMine->count() === 0)
        <div class="hint-text">No bookings in this range.</div>
    @else
        @foreach($mineGrouped as $date => $rows)
            <div style="margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                <div class="hint-text"><strong>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</strong></div>
                @foreach($rows as $b)
                    @php
                        $rowTz = isset($b->timezone) && trim((string) $b->timezone) !== '' ? (string) $b->timezone : $companyTimezone;
                        $startLocal = \Carbon\Carbon::parse($b->planned_start)->utc()->timezone($rowTz)->format('g:i A');
                        $endLocal = \Carbon\Carbon::parse($b->planned_end)->utc()->timezone($rowTz)->format('g:i A');
                        $endUtc = \Carbon\Carbon::parse($b->planned_end)->utc();
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
    @elseif($bookingsOther->count() === 0)
        <div class="hint-text">No other bookings in this range.</div>
    @else
        @foreach($otherGrouped as $date => $rows)
            <div style="margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                <div class="hint-text"><strong>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</strong></div>
                @foreach($rows as $b)
                    @php
                        $rowTz = isset($b->timezone) && trim((string) $b->timezone) !== '' ? (string) $b->timezone : $companyTimezone;
                        $startLocal = \Carbon\Carbon::parse($b->planned_start)->utc()->timezone($rowTz)->format('g:i A');
                        $endLocal = \Carbon\Carbon::parse($b->planned_end)->utc()->timezone($rowTz)->format('g:i A');
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
