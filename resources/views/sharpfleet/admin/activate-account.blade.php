@extends('layouts.sharpfleet')

@section('title', 'Complete Your Registration - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Complete Your Registration</h1>
            <p class="auth-subtitle">Hi {{ $first_name }}, are you setting this up as a sole trader or company?</p>
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

        <form method="POST" action="/app/sharpfleet/activate/complete" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Business Type Selection --}}
            <div class="form-section">
                <p class="section-subtitle">This helps us configure your account appropriately.</p>

                <div class="business-type-options">
                    <div class="business-option">
                        <input type="radio" id="sole_trader" name="business_type" value="sole_trader" required>
                        <label for="sole_trader" class="business-card">
                            <div class="business-icon">👤</div>
                            <h4>Sole Trader</h4>
                            <p>You'll be the admin and can add drivers later if needed. Perfect for individual operators.</p>
                        </label>
                    </div>

                    <div class="business-option">
                        <input type="radio" id="company" name="business_type" value="company" required>
                        <label for="company" class="business-card">
                            <div class="business-icon">🏢</div>
                            <h4>Company</h4>
                            <p>You'll manage a team with multiple drivers. Ideal for businesses with fleet operations.</p>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Password Setup --}}
            <div class="form-section">
                <h3 class="section-title">Create Your Password</h3>

                <!-- New reusable component -->
                <x-password-field
                    label="Password"
                    name="password"
                    confirm="password_confirmation"
                    show-generator="true"
                    show-strength="true"
                />
            </div>

            <button type="submit" class="btn-sf-navy btn-full">
                Complete Registration
            </button>
        </form>

        <div class="auth-footer">
            <p>The activation link will expire in 24 hours.</p>
        </div>
    </div>
</div> 

@endsection