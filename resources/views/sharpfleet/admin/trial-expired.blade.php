@extends('layouts.sharpfleet')

@section('title', 'Trial Expired - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Trial Period Ended</h1>
            <p class="auth-subtitle">Your 30-day free trial has expired</p>
        </div>

        <div class="auth-content">
            @if (session('warning'))
                <div class="alert alert-error">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="alert alert-error" style="align-items:flex-start;">
                <div class="d-flex justify-between align-items-center w-100 flex-wrap gap-2">
                    <div>
                        <div class="fw-bold mb-1">Trial ended</div>
                        <div class="small">Subscribe to regain full access. You can still view trip reports.</div>
                    </div>
                    <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/account">Go to Account</a>
                </div>
            </div>

            <div class="trial-summary">
                <h3>What you can still do:</h3>
                <ul>
                    <li>✅ View and export trip reports</li>
                    <li>✅ Access all your historical data</li>
                    <li>✅ Login to your account</li>
                    <li>✅ View vehicle information</li>
                </ul>

                <h3>What requires a subscription:</h3>
                <ul>
                    <li>❌ Start new trips</li>
                    <li>❌ Add/edit vehicles</li>
                    <li>❌ Manage company settings</li>
                    <li>❌ Add new users</li>
                </ul>
            </div>

            <div class="upgrade-options mt-4">
                <h3>Subscription estimate</h3>

                <div class="stats-card text-left" style="margin-top: 12px;">
                    <div class="d-flex justify-between align-items-center flex-wrap gap-2 mb-2">
                        <div>
                            <div class="fw-bold">Based on your vehicles</div>
                            <div class="text-muted small">Per-vehicle pricing scale (monthly)</div>
                        </div>
                        <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/account">Subscribe</a>
                    </div>

                    <div class="grid grid-2" style="gap: 16px;">
                        <div class="stats-card" style="margin:0;">
                            <div class="stats-number">{{ (int) ($vehiclesCount ?? 0) }}</div>
                            <div class="stats-label">Active vehicles</div>
                        </div>
                        <div class="stats-card" style="margin:0;">
                            <div class="stats-number">${{ number_format((float) ($monthlyPrice ?? 0), 2) }}</div>
                            <div class="stats-label">Estimated monthly cost</div>
                        </div>
                    </div>

                    <div class="text-muted small mt-2">
                        $3.50 per vehicle/month for vehicles 1–10, then $2.50 per vehicle/month for vehicles 11–20 ({{ $monthlyPriceBreakdown ?? '' }}).
                        @if(($requiresContactForPricing ?? false))
                            <div class="mt-1">Over 20 vehicles: please <a href="mailto:info@sharplync.com.au">contact us</a> for pricing.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="btn-group justify-center mt-4">
                <a href="/app/sharpfleet/admin/reports" class="btn btn-secondary">
                    View Trip Reports
                </a>
                <form method="POST" action="/app/sharpfleet/admin/logout">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection