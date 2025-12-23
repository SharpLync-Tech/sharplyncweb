@extends('layouts.sharpfleet')

@section('title', 'Driver Dashboard')

@section('sharpfleet-content')
@php
    use Illuminate\Support\Facades\DB;
    use App\Models\SharpFleet\Trip;
    use Carbon\Carbon;

    $user = session('sharpfleet.user');

    $vehicles = DB::connection('sharpfleet')
        ->table('vehicles')
        ->where('organisation_id', $user['organisation_id'])
        ->where('is_active', 1)
        ->orderBy('name')
        ->get();

    // Detect active trip for this driver
    $activeTrip = Trip::where('organisation_id', $user['organisation_id'])
        ->where('user_id', $user['id'])
        ->whereNull('ended_at')
        ->first();
@endphp

<div style="max-width:720px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:10px;">Driver Dashboard</h1>

    {{-- Flash message --}}
    @if (session('success'))
        <div style="background:#d1fae5;
                    border:1px solid #10b981;
                    padding:12px;
                    border-radius:8px;
                    margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="background:#f8f9fb;padding:16px;border-radius:8px;margin-bottom:24px;">
        <strong>{{ $user['name'] }}</strong><br>
        {{ $user['email'] }}<br>
        Organisation ID: {{ $user['organisation_id'] }}
    </div>

    @if ($activeTrip)

        {{-- ACTIVE TRIP CARD --}}
        <div style="background:#e6fffa;
                    border:1px solid #38b2ac;
                    padding:20px;
                    border-radius:10px;
                    margin-bottom:24px;">

            <h2 style="margin-bottom:12px;">🚗 Trip in progress</h2>

            <p style="margin-bottom:6px;">
                <strong>Vehicle:</strong>
                {{ optional($activeTrip->vehicle)->name ?? 'Vehicle #' . $activeTrip->vehicle_id }}
            </p>

            <p style="margin-bottom:6px;">
                <strong>Started:</strong>
                {{ Carbon::parse($activeTrip->start_time)->format('d/m/Y H:i') }}
            </p>

            <p style="margin-bottom:0;">
                <strong>Start KM:</strong>
                {{ number_format($activeTrip->start_km) }}
            </p>
        </div>

        {{-- END TRIP CARD --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);">

            <h2 style="margin-bottom:16px;">End Trip</h2>

            <form method="POST" action="/app/sharpfleet/trips/end">
                @csrf

                <input type="hidden" name="trip_id" value="{{ $activeTrip->id }}">

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">
                        Ending odometer (km)
                    </label>
                    <input type="number"
                           name="end_km"
                           inputmode="numeric"
                           required
                           placeholder="e.g. 125200"
                           style="width:100%;padding:12px;font-size:16px;">
                </div>

                <button type="submit"
                        style="width:100%;
                               background:#d9534f;
                               color:white;
                               border:none;
                               padding:14px;
                               font-size:16px;
                               font-weight:600;
                               border-radius:6px;">
                    End Trip
                </button>
            </form>
        </div>

    @else

        {{-- START TRIP CARD --}}
        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);">

            <h2 style="margin-bottom:16px;">Start a Trip</h2>

            <form method="POST" action="/app/sharpfleet/trips/start">
                @csrf

                {{-- Vehicle --}}
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">
                        Vehicle
                    </label>
                    <select name="vehicle_id" required
                            style="width:100%;padding:12px;font-size:16px;">
                        <option value="">Select vehicle</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Trip type --}}
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">
                        Trip type
                    </label>

                    <label style="display:block;margin-bottom:6px;">
                        <input type="radio" name="trip_mode" value="client" checked>
                        Client / business trip
                    </label>

                    <label style="display:block;">
                        <input type="radio" name="trip_mode" value="no_client">
                        No client / internal
                    </label>
                </div>

                {{-- Start KM --}}
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">
                        Starting odometer (km)
                    </label>
                    <input type="number"
                           name="start_km"
                           inputmode="numeric"
                           required
                           placeholder="e.g. 124500"
                           style="width:100%;padding:12px;font-size:16px;">
                </div>

                {{-- Submit --}}
                <button type="submit"
                        style="width:100%;
                               background:#2CBFAE;
                               color:white;
                               border:none;
                               padding:14px;
                               font-size:16px;
                               font-weight:600;
                               border-radius:6px;">
                    Start Trip
                </button>
            </form>
        </div>

    @endif

    {{-- Logout --}}
    <div style="margin-top:24px;">
        <form method="POST" action="/app/sharpfleet/logout">
            @csrf
            <button type="submit"
                    style="background:#d9534f;color:white;
                           border:none;padding:10px 16px;
                           border-radius:6px;">
                Log out
            </button>
        </form>
    </div>

</div>
@endsection
