@extends('layouts.sharpfleet')

@section('title', 'Reset Password - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container sharpfleet-login">
    <div class="auth-card max-w-420">
        <div class="auth-header">
            <div class="auth-icon">
                <img src="{{ asset('images/login-lock.png') }}" alt="Reset Password">
            </div>
            <h1 class="auth-title">Choose a New Password</h1>
        </div>

        <form method="POST" action="/app/sharpfleet/password/reset" class="auth-form">
            @csrf

            @if (session('error'))
                <div class="alert alert-error mb-3">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-section" style="margin-bottom: 0;">
                <x-password-field
                    label="New Password"
                    name="password"
                    confirm="password_confirmation"
                    show-generator="true"
                    show-strength="true"
                />
            </div>

            <button type="submit" class="btn btn-primary btn-full">Reset Password</button>
        </form>

        <div class="auth-footer">
            <a href="/app/sharpfleet/login">Back to login</a>
        </div>
    </div>
</div>
@endsection
