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
            Step {{ (int) ($step ?? 2) }} of {{ (int) ($totalSteps ?? 10) }} — Passenger/client presence.
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

    <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/settings/presence') }}">
        @csrf

        <div class="card sf-setup-card">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif
            <div class="card-header">
                <h2 class="card-title">Passenger / Client Presence</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Indicate whether a passenger or client was present during the trip.
                    This information may be used for compliance, billing, or reporting purposes.
                </p>

                <div class="text-muted small mb-3">
                    If you leave this disabled, drivers will not see any passenger/client presence questions.
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label d-flex align-items-start gap-2">
                        <input class="mt-1" type="checkbox" name="enable_client_presence" value="1"
                               {{ ($settings['client_presence']['enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="flex-grow-1">
                            <div><strong>Enable passenger/client presence tracking</strong></div>
                            <div class="text-muted small">Shows a simple “Was a passenger/client present?” prompt to drivers.</div>
                        </div>
                    </label>

                    <label class="checkbox-label d-flex align-items-start gap-2">
                        <input class="mt-1" type="checkbox" name="require_client_presence" value="1"
                               {{ ($settings['client_presence']['required'] ?? false) ? 'checked' : '' }}>
                        <div class="flex-grow-1">
                            <div><strong>Block trip start unless passenger/client presence is recorded</strong></div>
                            <div class="text-muted small">When enabled, drivers must answer the question before the trip can start.</div>
                        </div>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Label shown to drivers</label>
                    <input type="text" name="client_label" value="{{ old('client_label', $settings['client_presence']['label'] ?? 'Client') }}" class="form-control" placeholder="Client">
                    <div class="form-hint">Use wording your team recognises (e.g. “Passenger”, “Client”, “Customer”).</div>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="{{ url('/app/sharpfleet/admin/setup/company') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>

        <div class="mt-4 text-muted small">
            Tip: For most fleets, leaving this optional (not blocking trip start) keeps driver friction low.
        </div>
    </form>
</div>
</div>

@endsection
