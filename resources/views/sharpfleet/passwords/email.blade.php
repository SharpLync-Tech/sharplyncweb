@extends('layouts.sharpfleet')

@section('title', 'Forgot Password - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container sharpfleet-login">
    <div class="auth-card max-w-420">
        <div class="auth-header">
            <div class="auth-icon">
                <img src="{{ asset('images/login-lock.png') }}" alt="Reset Password">
            </div>
            <h1 class="auth-title">Reset Your Password</h1>
            <p class="auth-subtitle">Enter your email and we’ll send you a reset link.</p>
        </div>

        <form method="POST" action="/app/sharpfleet/password/email" class="auth-form">
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

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email') }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            <a href="/app/sharpfleet/login">Back to login</a>
        </div>
    </div>
</div>
@endsection
