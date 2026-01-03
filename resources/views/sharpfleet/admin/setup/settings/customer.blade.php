@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

@php
    $settings = array_replace_recursive([
        'client_presence' => [
            'enabled'  => false,
            'required' => false,
            'label'    => 'Client',
        ],
        'customer' => [
            'enabled'      => false,
            'allow_select' => true,
            'allow_manual' => true,
        ],
    ], $settings ?? []);
@endphp

<div class="sf-setup-backdrop" aria-hidden="true"></div>

<div class="sf-setup-layer">
<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">
            Step {{ (int) ($step ?? 3) }} of {{ (int) ($totalSteps ?? 10) }} — Customer/client capture.
        </p>
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

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/customer') }}">
        @csrf

        <div class="card sf-setup-card">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif
            <div class="card-header">
                <h2 class="card-title">Customer / Client</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">
                    Optional customer capture. Drivers can select a customer from your list or type a new name.
                    This never blocks a trip from starting.
                </p>

                <div class="text-muted small mb-3">
                    If you don’t need customer names on trips (for example, you only track internal travel), leave this disabled.
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_customer_capture" value="1"
                               {{ ($settings['customer']['enabled'] ?? false) ? 'checked' : '' }}>
                        <strong>Enable customer selection/entry on client trips</strong>
                        <div class="text-muted small ms-4">Adds an optional customer field drivers can fill in for reporting/billing.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_customer_select" value="1"
                               {{ ($settings['customer']['allow_select'] ?? true) ? 'checked' : '' }}>
                        <strong>Allow selecting from admin customer list</strong>
                        <div class="text-muted small ms-4">Drivers can pick from customers you manage in SharpFleet.</div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_customer_manual" value="1"
                               {{ ($settings['customer']['allow_manual'] ?? true) ? 'checked' : '' }}>
                        <strong>Allow manual customer name entry (not in list)</strong>
                        <div class="text-muted small ms-4">Drivers can type a new customer name if it’s not in your list.</div>
                    </label>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/settings/presence') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>

        <div class="mt-4 text-muted small">
            Tip: If you enable this, keep it optional to reduce driver friction.
        </div>
    </form>
</div>
</div>

@endsection
