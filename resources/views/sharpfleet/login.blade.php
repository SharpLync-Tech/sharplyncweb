@extends('layouts.sharpfleet')

@section('title', 'SharpFleet - Login')

@section('sharpfleet-content')
<div class="auth-container sharpfleet-login">
    <div class="auth-card max-w-420">
        <div class="auth-header">
            <div class="auth-icon">
                <img src="{{ asset('images/login-lock.png') }}" alt="Login">
            </div>
            <h1 class="auth-title">Sign In to Your Account</h1>
        </div>

        <form method="POST" action="/app/sharpfleet/login" class="auth-form">
            @csrf

            @if($errors->any())
                <div class="alert alert-error mb-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <div class="form-group remember-row">
                <label class="checkbox-label" for="remember">
                    <input type="checkbox" name="remember" id="remember" value="1">
                    <strong>Remember me</strong>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Log In</button>
        </form>

        <div class="auth-footer">
            Don’t have an account yet?
            <a href="/app/sharpfleet/admin/register">Create one here</a>
        </div>
    </div>
</div>
@endsection
