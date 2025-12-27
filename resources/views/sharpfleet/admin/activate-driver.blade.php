@extends('layouts.sharpfleet')

@section('title', 'Accept Invitation - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Accept Invitation</h1>
            @if(!empty($organisationName))
                <p class="auth-subtitle">You\'re joining {{ $organisationName }} as a driver.</p>
            @else
                <p class="auth-subtitle">Set your details to activate your driver account.</p>
            @endif
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

        <form method="POST" action="/app/sharpfleet/invite/complete" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-section">
                <h3 class="section-title">Your details</h3>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" value="{{ $email ?? '' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">First name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Last name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Create your password</h3>

                <x-password-field
                    label="Password"
                    name="password"
                    confirm="password_confirmation"
                    show-generator="true"
                    show-strength="true"
                />
            </div>

            <button type="submit" class="btn btn-primary btn-full">Activate Driver Account</button>
        </form>

        <div class="auth-footer">
            <p>This invitation link will expire for your security.</p>
        </div>
    </div>
</div>
@endsection
