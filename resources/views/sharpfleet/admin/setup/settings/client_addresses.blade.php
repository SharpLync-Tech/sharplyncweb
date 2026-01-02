@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

@php
    $settings = array_replace_recursive([
        'client_presence' => [
            'enable_addresses' => false,
        ],
    ], $settings ?? []);
@endphp

<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 6) }} of {{ (int) ($totalSteps ?? 9) }} — Client addresses.</p>
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

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/client-addresses') }}">
        @csrf

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Client Address Tracking</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Enable this if your business needs to record client addresses for billing or job tracking.
                    If you don’t need addresses, leave it off for privacy.
                </p>

                <label class="checkbox-label">
                    <input type="checkbox" name="enable_client_addresses" value="1"
                           {{ ($settings['client_presence']['enable_addresses'] ?? false) ? 'checked' : '' }}>
                    <strong>Allow recording client addresses</strong>
                    <div class="text-muted small ms-4">Drivers can record an address against a client trip when required by your workflow.</div>
                </label>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/reminders') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>

@endsection
