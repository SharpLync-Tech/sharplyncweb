@extends('layouts.sharpfleet')

@section('title', 'Trial Expired - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Trial Period Ended</h1>
            <p class="auth-subtitle">Your 30-day free trial has expired</p>
        </div>

        <div class="trial-expired-content" style="padding: 30px;">
            @if (session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

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

            <div class="upgrade-options" style="margin-top: 30px;">
                <h3>Ready to continue?</h3>
                <p>Choose a plan below to keep using all SharpFleet features:</p>

                <div class="billing-plans" style="margin-top: 20px;">
                    <div class="plan-option">
                        <div class="plan-card">
                            <div class="plan-header">
                                <h4>Starter</h4>
                                <div class="plan-price">
                                    <span class="price">$29</span>
                                    <span class="period">/month</span>
                                </div>
                            </div>
                            <div class="plan-features">
                                <ul>
                                    <li>Up to 3 vehicles</li>
                                    <li>Basic trip logging</li>
                                    <li>Email support</li>
                                </ul>
                            </div>
                            <button class="btn btn-primary btn-full" style="margin-top: 16px;">
                                Upgrade to Starter
                            </button>
                        </div>
                    </div>

                    <div class="plan-option">
                        <div class="plan-card">
                            <div class="plan-header">
                                <h4>Professional</h4>
                                <div class="plan-price">
                                    <span class="price">$59</span>
                                    <span class="period">/month</span>
                                </div>
                            </div>
                            <div class="plan-features">
                                <ul>
                                    <li>Up to 10 vehicles</li>
                                    <li>Advanced features</li>
                                    <li>Priority support</li>
                                </ul>
                            </div>
                            <button class="btn btn-primary btn-full" style="margin-top: 16px;">
                                Upgrade to Professional
                            </button>
                        </div>
                    </div>

                    <div class="plan-option">
                        <div class="plan-card">
                            <div class="plan-header">
                                <h4>Enterprise</h4>
                                <div class="plan-price">
                                    <span class="price">$99</span>
                                    <span class="period">/month</span>
                                </div>
                            </div>
                            <div class="plan-features">
                                <ul>
                                    <li>Unlimited vehicles</li>
                                    <li>All features</li>
                                    <li>Dedicated support</li>
                                </ul>
                            </div>
                            <button class="btn btn-primary btn-full" style="margin-top: 16px;">
                                Upgrade to Enterprise
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="trial-actions" style="margin-top: 30px; text-align: center;">
                <a href="/app/sharpfleet/admin/reports" class="btn btn-secondary">
                    View Trip Reports
                </a>
                <form method="POST" action="/app/sharpfleet/admin/logout" style="display: inline; margin-left: 10px;">
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