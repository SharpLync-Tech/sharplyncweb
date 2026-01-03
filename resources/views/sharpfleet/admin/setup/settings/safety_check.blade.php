@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

@php
    $settings = array_replace_recursive([
        'safety_check' => [
            'enabled' => false,
            'items' => [],
        ],
    ], $settings ?? []);

    $safetyItems = $settings['safety_check']['items'] ?? [];
    $safetyCount = is_array($safetyItems) ? count($safetyItems) : 0;
@endphp

<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 7) }} of {{ (int) ($totalSteps ?? 9) }} — Pre-drive safety check.</p>
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

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/safety-check') }}">
        @csrf

        <div class="card sf-setup-card">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif
            <div class="card-header">
                <h2 class="card-title">Pre-Drive Safety Check</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Enable this if drivers must complete a checklist before starting trips.
                    This is common for compliance-heavy fleets.
                </p>

                <label class="checkbox-label">
                    <input type="checkbox" name="enable_safety_check" value="1"
                           {{ ($settings['safety_check']['enabled'] ?? false) ? 'checked' : '' }}>
                    <strong>Enable safety check before trips</strong>
                    <div class="text-muted small ms-4">When enabled, drivers will be prompted to complete your checklist.</div>
                </label>

                <p class="text-muted ms-4">
                    @if($safetyCount > 0)
                        Checklist items configured: <strong>{{ $safetyCount }}</strong>.
                        <a href="{{ url('/app/sharpfleet/admin/safety-checks') }}">Edit checklist</a>
                    @else
                        No safety checklist has been configured yet.
                        <a href="{{ url('/app/sharpfleet/admin/safety-checks') }}">Configure checklist</a>
                    @endif
                </p>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/client-addresses') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>

@endsection
