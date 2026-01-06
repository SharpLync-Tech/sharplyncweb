@extends('layouts.sharpfleet')

@section('title', 'Complete Your Registration - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Complete Your Registration</h1>
            <p class="auth-subtitle">Hi {{ $first_name }}, set your password to finish activating your account.</p>
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
            <p>Next you’ll choose your account type in the setup wizard.</p>
        </div>
    </div>
</div>

@endsection