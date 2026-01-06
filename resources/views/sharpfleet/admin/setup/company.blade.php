@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

<div class="sf-setup-backdrop" aria-hidden="true"></div>

<div class="sf-setup-layer">
<div class="container">
    @php
        $sfOrgId = (int) ($organisation->id ?? 0);
        $sfAccountType = \App\Support\SharpFleet\OrganisationAccount::forOrganisationId($sfOrgId);

        $stepTitle = 'Company details';
        $nameLabel = 'Company name';
        $nameHint = 'Tip: use the trading name your drivers recognise.';
        $introText = 'These details are used across SharpFleet (emails, reports, and date/time display).';

        if ($sfAccountType === \App\Support\SharpFleet\OrganisationAccount::TYPE_PERSONAL) {
            $stepTitle = 'Time zone';
            $introText = 'This controls how dates and times are shown throughout SharpFleet.';
        } elseif ($sfAccountType === \App\Support\SharpFleet\OrganisationAccount::TYPE_SOLE_TRADER) {
            $stepTitle = 'Business details';
            $nameLabel = 'Business name';
            $nameHint = 'Tip: use your trading name.';
            $introText = 'These details are used across SharpFleet (emails, reports, and date/time display).';
        }
    @endphp

    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">
            Step {{ (int) ($step ?? 2) }} of {{ (int) ($totalSteps ?? 11) }} — {{ $stepTitle }}.
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
            <div class="text-muted mb-3">
                {{ $introText }}
            </div>

            @if($sfAccountType === \App\Support\SharpFleet\OrganisationAccount::TYPE_PERSONAL)
                @php
                    $sfUser = session('sharpfleet.user');
                    $sfFirstName = '';
                    if (is_array($sfUser)) {
                        $sfFirstName = trim((string) ($sfUser['first_name'] ?? ''));
                    }
                @endphp
                <div class="mb-3">
                    Hi {{ $sfFirstName !== '' ? $sfFirstName : 'there' }}, welcome to SharpFleet.
                </div>
            @endif

            <form method="POST" action="/app/sharpfleet/admin/setup/company">
                @csrf

                @if($sfAccountType === \App\Support\SharpFleet\OrganisationAccount::TYPE_COMPANY || $sfAccountType === \App\Support\SharpFleet\OrganisationAccount::TYPE_SOLE_TRADER)
                    <div class="mb-3">
                        <label class="form-label">{{ $nameLabel }}</label>
                        <input type="text" name="company_name" class="form-control"
                               value="{{ old('company_name', $organisation->name ?? '') }}" required>
                        <div class="form-hint">{{ $nameHint }}</div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Time zone</label>
                    <select name="timezone" class="form-control" required>
                        @php($selectedTimezone = (string) old('timezone', (string) ($settings['timezone'] ?? 'Australia/Brisbane')))
                        @include('sharpfleet.partials.timezone-options', ['selectedTimezone' => $selectedTimezone])
                    </select>
                </div>

                @if($sfAccountType !== \App\Support\SharpFleet\OrganisationAccount::TYPE_PERSONAL)
                    <div class="mb-3">
                        <label class="form-label">Industry (optional)</label>
                        <input type="text" name="industry" class="form-control"
                               value="{{ old('industry', $settings['industry'] ?? '') }}"
                               placeholder="e.g. Trades, Facilities, Transport">
                        <div class="form-hint">Used for internal reference and onboarding only.</div>
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

@endsection
