@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

@php
    $settings = array_replace_recursive([
        'trip' => [
            'odometer_required' => true,
            'odometer_allow_override' => true,
            'allow_private_trips' => false,
            'require_manual_start_end_times' => false,
        ],
    ], $settings ?? []);
@endphp

<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 3) }} of {{ (int) ($totalSteps ?? 9) }} — Trip rules.</p>
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

    @php($setupImgPath = public_path('images/sharpfleet/setup.png'))

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/trip-rules') }}">
        @csrf

        <div class="card sf-setup-card">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif
            <div class="card-header">
                <h2 class="card-title">Trip Rules</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    These settings control what drivers must capture when starting and ending trips.
                    Choose stricter rules if you need stronger compliance; choose simpler rules for faster driver workflows.
                </p>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="require_odometer_start" value="1"
                               {{ ($settings['trip']['odometer_required'] ?? true) ? 'checked' : '' }}>
                        <strong>Require starting reading when starting a trip (km or hours)</strong>
                        <div class="text-muted small ms-4">Ensures every trip has a starting reading for accurate distance/usage tracking.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_odometer_override" value="1"
                               {{ ($settings['trip']['odometer_allow_override'] ?? true) ? 'checked' : '' }}>
                        <strong>Allow drivers to override the auto-filled reading (km or hours)</strong>
                        <div class="text-muted small ms-4">Useful when the last reading is wrong or the vehicle’s display was missed previously.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_private_trips" value="1"
                               {{ filter_var(($settings['trip']['allow_private_trips'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                        <strong>Allow your drivers to use the vehicle for private use</strong>
                        <div class="text-muted small ms-4">Enables a private trip type so personal use can be tracked separately.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="require_manual_start_end_times" value="1"
                               {{ filter_var(($settings['trip']['require_manual_start_end_times'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                        <strong>Require drivers to enter a start time and end time for each trip</strong>
                        <div class="text-muted small ms-4">Adds extra detail for auditing, but increases time-to-start for drivers.</div>
                    </label>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/presence') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>

@endsection
