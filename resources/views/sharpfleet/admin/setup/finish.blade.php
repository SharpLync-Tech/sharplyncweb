@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

<div class="sf-setup-backdrop" aria-hidden="true"></div>

<div class="sf-setup-layer">
<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">Step {{ (int) ($step ?? 9) }} of {{ (int) ($totalSteps ?? 9) }} — Finish.</p>
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

    <div class="card sf-setup-card" style="max-width: 840px;">
        @if (is_string($setupImgPath) && file_exists($setupImgPath))
            <div class="sf-setup-card__cover" aria-hidden="true">
                <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
            </div>
        @endif

        <div class="card-body">
            <p class="text-muted mb-3">
                You’re ready to start using SharpFleet.
                Click “Finish setup” to lock in these choices and continue to the admin dashboard.
            </p>

            <div class="text-muted small mb-4">
                You can always change these later under Company Settings.
            </div>

            <form method="POST" action="{{ url('/app/sharpfleet/admin/setup/finish') }}">
                @csrf

                <div class="btn-group">
                    <a href="{{ url('/app/sharpfleet/admin/setup/settings/incident-reporting') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Finish setup</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

@endsection
