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

            <button type="submit" class="btn btn-primary btn-full">
                Complete Registration
            </button>
        </form>

        <div class="auth-footer">
            <p>The activation link will expire in 24 hours.</p>
        </div>
    </div>
</div>

<style>
.business-type-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.business-option {
    position: relative;
}

.business-card {
    display: block;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.business-card:hover {
    border-color: #2CBFAE;
    box-shadow: 0 2px 8px rgba(44, 191, 174, 0.1);
}

.business-option input[type="radio"]:checked + .business-card {
    border-color: #2CBFAE;
    background: #f8fffe;
}

.business-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.business-card h4 {
    margin: 10px 0;
    color: #333;
}

.business-card p {
    margin: 0;
    font-size: 14px;
    color: #666;
    line-height: 1.4;
}

@media (max-width: 768px) {
    .business-type-options {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection