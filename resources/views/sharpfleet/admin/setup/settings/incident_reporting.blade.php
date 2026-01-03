@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

@php
    $settings = array_replace_recursive([
        'faults' => [
            'enabled' => false,
            'allow_during_trip' => true,
            'require_end_of_trip_check' => false,
        ],
    ], $settings ?? []);
@endphp

<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 8) }} of {{ (int) ($totalSteps ?? 9) }} — Vehicle issue/accident reporting.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    @php
        $setupImgPath = public_path('images/sharpfleet/setup.png');
    @endphp

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/incident-reporting') }}">
        @csrf

        <div class="card sf-setup-card">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif
            <div class="card-header">
                <h2 class="card-title">Vehicle Issue / Accident Reporting</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Enable this if drivers need to report vehicle issues or accidents against a vehicle.
                    When enabled, drivers will see a “Report a Vehicle Issue / Accident” option in their portal.
                </p>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_fault_reporting" value="1"
                               {{ ($settings['faults']['enabled'] ?? false) ? 'checked' : '' }}>
                           <strong>Enable vehicle issue/accident reporting</strong>
                           <div class="text-muted small ms-4">Allows drivers to create incident reports linked to a vehicle.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_fault_during_trip" value="1"
                               {{ ($settings['faults']['allow_during_trip'] ?? true) ? 'checked' : '' }}>
                           <strong>Allow drivers to report issues/accidents during a trip</strong>
                           <div class="text-muted small ms-4">Useful when something happens mid-trip and can’t wait until the end.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="require_end_of_trip_fault_check" value="1"
                               {{ ($settings['faults']['require_end_of_trip_check'] ?? false) ? 'checked' : '' }}>
                        <strong>Require a quick issue/accident check when ending a trip (coming soon)</strong>
                        <div class="text-muted small ms-4">Planned feature — safe to leave off unless you want this workflow later.</div>
                    </label>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/safety-check') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>

@endsection
