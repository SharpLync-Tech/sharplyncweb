@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

<div class="sf-setup-backdrop" aria-hidden="true"></div>

<div class="sf-setup-layer">
<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">
            Step {{ (int) ($step ?? 1) }} of {{ (int) ($totalSteps ?? 11) }} — Account type.
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
        $selected = (string) ($selectedAccountType ?? old('account_type', 'company'));
    @endphp

    <form method="POST" action="/app/sharpfleet/admin/setup/account-type">
        @csrf

        <div class="card sf-setup-card" style="max-width: 840px;">
            @if (is_string($setupImgPath) && file_exists($setupImgPath))
                <div class="sf-setup-card__cover" aria-hidden="true">
                    <img src="{{ asset('images/sharpfleet/setup.png') }}?v={{ @filemtime($setupImgPath) ?: time() }}" alt="">
                </div>
            @endif

            <div class="card-header">
                <h2 class="card-title">Choose your account type</h2>
            </div>

            <div class="card-body">
                <p class="text-muted mb-3">
                    This controls which SharpFleet features are shown to you.
                </p>

                <div class="business-type-options">
                    <div class="business-option">
                        <input type="radio" id="account_personal" name="account_type" value="personal" {{ $selected === 'personal' ? 'checked' : '' }} required>
                        <label for="account_personal" class="business-card">
                            <div class="business-icon">👤</div>
                            <h4>Personal</h4>
                            <p>Simple setup for individual use. No bookings, no users, no customers.</p>
                        </label>
                    </div>

                    <div class="business-option">
                        <input type="radio" id="account_sole" name="account_type" value="sole_trader" {{ $selected === 'sole_trader' ? 'checked' : '' }} required>
                        <label for="account_sole" class="business-card">
                            <div class="business-icon">🧰</div>
                            <h4>Sole Trader</h4>
                            <p>For a single operator. No bookings, no users. Customers optional.</p>
                        </label>
                    </div>

                    <div class="business-option">
                        <input type="radio" id="account_company" name="account_type" value="company" {{ $selected === 'company' ? 'checked' : '' }} required>
                        <label for="account_company" class="business-card">
                            <div class="business-icon">🏢</div>
                            <h4>Company</h4>
                            <p>Multi-user fleet operations. Includes bookings and user management.</p>
                        </label>
                    </div>
                </div>

                <div class="mt-3 text-muted small">
                    You can add vehicles later under <strong>Fleet → Vehicles</strong>.
                </div>
            </div>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>
</div>

@endsection
