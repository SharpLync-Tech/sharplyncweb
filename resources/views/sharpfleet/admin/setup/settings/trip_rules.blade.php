@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')
<style>
    .sf-tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .sf-tooltip__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        margin-left: 6px;
        border-radius: 50%;
        border: 1px solid rgba(27, 165, 165, 0.35);
        background: rgba(27, 165, 165, 0.08);
        color: #1ba5a5;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
        cursor: help;
        user-select: none;
        transition: background-color 120ms ease, border-color 120ms ease, color 120ms ease, transform 80ms ease;
    }

    .sf-tooltip__icon:hover {
        background: #1ba5a5;
        border-color: #1ba5a5;
        color: #ffffff;
    }

    .sf-tooltip__icon:active {
        transform: scale(0.95);
    }

    .sf-tooltip__icon:focus-visible {
        outline: 2px solid rgba(27, 165, 165, 0.45);
        outline-offset: 2px;
    }

    .sf-tooltip__content {
        position: absolute;
        left: 0;
        top: calc(100% + 8px);
        min-width: 280px;
        max-width: 380px;
        padding: 12px 14px;
        border-radius: 10px;
        background: #0f2f2f;
        color: #e9f7f7;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        font-size: 13px;
        line-height: 1.45;
        z-index: 20;
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity 140ms ease, transform 140ms ease;
    }

    .sf-tooltip__content strong {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #bff3f3;
    }

    .sf-tooltip__content span {
        display: block;
        margin-top: 6px;
        color: #d9f1f1;
    }

    .sf-tooltip:hover .sf-tooltip__content,
    .sf-tooltip:focus-within .sf-tooltip__content {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    @media (max-width: 640px) {
        .sf-tooltip__content {
            left: auto;
            right: 0;
            max-width: min(92vw, 360px);
        }
    }
</style>

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

<div class="sf-setup-backdrop" aria-hidden="true"></div>

<div class="sf-setup-layer">
<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 4) }} of {{ (int) ($totalSteps ?? 10) }} — Trip rules.</p>
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
                        <strong>Allow private use of fleet vehicles</strong>
                        <div class="text-muted small ms-4">Enables a private trip type when a fleet vehicle is used for personal use.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_private_vehicle_slots" value="1"
                               {{ filter_var(($settings['trip']['private_vehicle_slots_enabled'] ?? false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                        <strong>Allow private vehicle use when fleet vehicles are unavailable</strong>
                        <span class="sf-tooltip" aria-label="What does this setting do?">
                            <span class="sf-tooltip__icon">ⓘ</span>
                            <span class="sf-tooltip__content" role="tooltip">
                                <strong>What does this setting do?</strong>
                                <span>Enable this setting to allow drivers to record trips using their own private vehicles when no fleet vehicles are available due to active trips, servicing, or repairs.</span>
                                <span>This ensures all trips are still logged correctly against jobs, customers, and drivers, even when a fleet vehicle cannot be used.</span>
                                <span>Private vehicle use is intended for occasional, real-world situations and is limited to prevent it from replacing fleet vehicles. Once the allowed number of private vehicle uses is reached, additional private vehicle trips cannot be started until an existing trip is completed.</span>
                            </span>
                        </span>
                        <div class="text-muted small ms-4">Shown only when all fleet vehicles are unavailable.</div>
                    </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="enable_purpose_of_travel" value="1" {{ !empty($settings['trip']['purpose_of_travel_enabled']) ? 'checked' : '' }}>
                            <strong>Enable Purpose of Travel (business trips)</strong>
                            <div class="text-muted small ms-4">Shows an optional 255 character text field when drivers start a business trip.</div>
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
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/customer') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>
</div>

@endsection
