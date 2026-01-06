@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

@php
    $settings = array_replace_recursive([
        'reminders' => [
            'registration_days' => 30,
            'service_days' => 30,
            'service_reading_threshold' => 500,
        ],
    ], $settings ?? []);
@endphp

<div class="sf-setup-backdrop" aria-hidden="true"></div>

<div class="sf-setup-layer">
<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 6) }} of {{ (int) ($totalSteps ?? 10) }} — Reminder emails.</p>
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

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/reminders') }}">
        @csrf

        <div class="card sf-setup-card">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif
            <div class="card-header">
                <h2 class="card-title">Reminder Emails</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    These settings control when SharpFleet marks vehicles as “Due soon” and when reminder emails are sent.
                    They apply at a company level (not per vehicle).
                </p>

                <div class="form-group">
                    <label class="form-label">Registration window (days)</label>
                    <input type="number" min="1" step="1" name="reminder_registration_days"
                           value="{{ old('reminder_registration_days', (int) ($settings['reminders']['registration_days'] ?? 30)) }}" class="form-control">
                    <div class="text-muted small mt-1">Example: 30 means “Due soon” starts 30 days before registration expires.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Service (date) window (days)</label>
                    <input type="number" min="1" step="1" name="reminder_service_days"
                           value="{{ old('reminder_service_days', (int) ($settings['reminders']['service_days'] ?? 30)) }}" class="form-control">
                    <div class="text-muted small mt-1">Example: 30 means “Due soon” starts 30 days before the service due date.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Service (reading) threshold</label>
                    <input type="number" min="0" step="1" name="reminder_service_reading_threshold"
                           value="{{ old('reminder_service_reading_threshold', (int) ($settings['reminders']['service_reading_threshold'] ?? 500)) }}" class="form-control">
                    <div class="text-muted small mt-1">If a vehicle is within this distance/hours of its due reading, it will be marked as “Due soon”.</div>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/vehicle-tracking') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>
</div>

@endsection
