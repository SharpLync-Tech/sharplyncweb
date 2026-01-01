@extends('layouts.sharpfleet')

@section('title', 'Account & Subscription')

@section('sharpfleet-content')
<div class="hero">
    <h1>Account &amp;<br><span class="highlight">Subscription</span></h1>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning">
        {{ session('warning') }}
    </div>
@endif

<div class="card">
    <div class="card-body">
        @if($isSubscribed)
            <div class="d-flex justify-between align-items-center flex-wrap gap-2 mb-2">
                <div>
                    <div class="fw-bold">Subscription active</div>
                    <div class="text-muted small">Billing is calculated per active vehicle.</div>
                </div>

                <form method="POST" action="/app/sharpfleet/admin/account/cancel-subscription">
                    @csrf
                    <button class="btn btn-danger" type="submit">Cancel Subscription</button>
                </form>
            </div>

            <div class="grid grid-2 mt-3">
                <div class="stats-card" style="margin:0;">
                    <div class="stats-number">{{ (int) $vehiclesCount }}</div>
                    <div class="stats-label">Active vehicles</div>
                </div>
                <div class="stats-card" style="margin:0;">
                    <div class="stats-number">${{ number_format((float) $monthlyPrice, 2) }}</div>
                    <div class="stats-label">Estimated monthly cost</div>
                </div>
            </div>

            <div class="text-muted small mt-2">
                Pricing: $3.50 per vehicle/month for vehicles 1–10, then $2.50 per vehicle/month for vehicles 11+ ({{ $monthlyPriceBreakdown }}).
                @if($requiresContactForPricing)
                    <div class="mt-1">
                        Over 20 vehicles: please <a href="mailto:info@sharplync.com.au">contact us</a> for pricing.
                    </div>
                @endif

                <div class="mt-2">
                    Cancelling your subscription switches your account to read-only access (reports only). You will have access to your data for one year, after that the account will be archived.
                </div>
            </div>
        @else
            <div class="d-flex justify-between align-items-center flex-wrap gap-2 mb-2">
                <div>
                    <div class="fw-bold">Free trial</div>
                    <div class="text-muted small">
                        @if(is_null($trialDaysRemaining))
                            Trial status unavailable.
                        @elseif($trialDaysRemaining > 0)
                            {{ (int) $trialDaysRemaining }} day(s) remaining.
                        @else
                            Trial ended.
                        @endif
                    </div>
                </div>

                <form method="POST" action="/app/sharpfleet/admin/account/subscribe">
                    @csrf
                    <button class="btn btn-primary" type="submit">Subscribe</button>
                </form>
            </div>

            @if($trialEndsAt)
                <div class="text-muted small mb-3">Trial ends: {{ $trialEndsAt->format('d M Y, H:i') }}</div>
            @endif

            @if($hasCancelRequest)
                <div class="alert alert-error mb-0">
                    Trial cancellation requested. Your account is now read-only (reports only).
                </div>
            @else
                <div class="alert alert-info" style="align-items:flex-start;">
                    <div>
                        <div class="fw-bold mb-1">Cancel trial</div>
                        <div class="small">
                            Cancelling your trial switches your account to read-only access (reports only). You will have access to your data for one year, after that the account will be archived.
                        </div>
                        <form method="POST" action="/app/sharpfleet/admin/account/cancel-trial" class="mt-2">
                            @csrf
                            <button class="btn btn-danger" type="submit">Cancel Trial</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="text-muted small mt-3">
                Pricing when subscribed: $3.50 per vehicle/month for vehicles 1–10, then $2.50 per vehicle/month for vehicles 11–20. Over 20 vehicles: contact us for pricing.
            </div>
        @endif
    </div>
</div>
@endsection
