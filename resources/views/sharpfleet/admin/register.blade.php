@extends('layouts.sharpfleet')

@section('title', 'Register for SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Start Your SharpFleet Trial</h1>
            <p class="auth-subtitle">30 days free • No credit card required</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/app/sharpfleet/admin/register" class="auth-form">
            @csrf

            {{-- Organisation Details --}}
            <div class="form-section">
                <h3 class="section-title">Organisation Details</h3>

                <div class="form-group">
                    <label class="form-label">Organisation Name</label>
                    <input type="text" name="organisation_name" class="form-control"
                           value="{{ old('organisation_name') }}" required>
                </div>
            </div>

            {{-- Personal Details --}}
            <div class="form-section">
                <h3 class="section-title">Your Details</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control"
                               value="{{ old('first_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control"
                               value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email') }}" required>
                    <div class="form-hint">This will be your login email</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-hint">Minimum 8 characters with uppercase, lowercase, and numbers</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            {{-- Billing Plan --}}
            <div class="form-section">
                <h3 class="section-title">Choose Your Plan</h3>
                <p class="section-subtitle">All plans include a 30-day free trial. No credit card required to start.</p>

                <div class="billing-plans">
                    <div class="plan-option">
                        <input type="radio" id="starter" name="billing_plan" value="starter"
                               {{ old('billing_plan', 'starter') === 'starter' ? 'checked' : '' }}>
                        <label for="starter" class="plan-card">
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
                                    <li>Mobile app access</li>
                                </ul>
                            </div>
                        </label>
                    </div>

                    <div class="plan-option">
                        <input type="radio" id="professional" name="billing_plan" value="professional"
                               {{ old('billing_plan') === 'professional' ? 'checked' : '' }}>
                        <label for="professional" class="plan-card">
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
                                    <li>Advanced trip logging</li>
                                    <li>Client management</li>
                                    <li>Priority support</li>
                                    <li>Custom reports</li>
                                </ul>
                            </div>
                        </label>
                    </div>

                    <div class="plan-option">
                        <input type="radio" id="enterprise" name="billing_plan" value="enterprise"
                               {{ old('billing_plan') === 'enterprise' ? 'checked' : '' }}>
                        <label for="enterprise" class="plan-card">
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
                                    <li>All Professional features</li>
                                    <li>API access</li>
                                    <li>White-label option</li>
                                    <li>Dedicated support</li>
                                </ul>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Terms and Conditions --}}
            <div class="form-section">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agree_terms" value="1"
                               {{ old('agree_terms') ? 'checked' : '' }} required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Start Free 30-Day Trial
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="/app/sharpfleet/admin/login">Sign in</a></p>
        </div>
    </div>
</div>
@endsection
