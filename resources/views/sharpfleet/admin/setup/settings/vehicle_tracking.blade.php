@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

@php
    $settings = array_replace_recursive([
        'vehicles' => [
            'registration_tracking_enabled' => true,
            'servicing_tracking_enabled' => false,
        ],
    ], $settings ?? []);
@endphp

<div class="sf-setup-backdrop" aria-hidden="true"></div>

<div class="sf-setup-layer">
<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 4) }} of {{ (int) ($totalSteps ?? 9) }} — Vehicle tracking.</p>
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

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/vehicle-tracking') }}">
        @csrf

        <div class="card sf-setup-card">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif
            <div class="card-header">
                <h2 class="card-title">Vehicle Tracking</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    These settings control which extra, admin-managed vehicle details are tracked.
                    Turn on only what you plan to maintain, so vehicle records stay accurate.
                </p>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_vehicle_registration_tracking" value="1"
                               {{ ($settings['vehicles']['registration_tracking_enabled'] ?? true) ? 'checked' : '' }}>
                        <strong>Enable Vehicle Registration Tracking</strong>
                        <div class="text-muted small ms-4">Adds registration fields (rego number, expiry date) on vehicle profiles and can drive reminder emails.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_vehicle_servicing_tracking" value="1"
                               {{ ($settings['vehicles']['servicing_tracking_enabled'] ?? false) ? 'checked' : '' }}>
                        <strong>Enable Vehicle Servicing Tracking</strong>
                        <div class="text-muted small ms-4">Tracks service due dates/readings on vehicles and can drive reminder emails.</div>
                    </label>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/trip-rules') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>
</div>

@endsection
