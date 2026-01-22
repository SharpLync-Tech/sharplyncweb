@extends('layouts.sharpfleet')

@section('title', 'Check Your Email - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Check Your Email</h1>
            <p class="auth-subtitle">You're almost set up</p>
        </div>

        <div class="auth-content">
            <div class="success-message">
                <div class="success-icon">📧</div>

                <h3>Registration successful</h3>

                <p>
                    We’ve sent an activation email to your email address.
                    Please check your inbox and click the activation link to
                    complete your registration.
                </p>

                <div class="info-box">
                    <p><strong>Can’t see the email?</strong></p>
                    <ul>
                        <li>Check your spam or junk folder</li>
                        <li>Give it a few minutes — sometimes it’s slow</li>
                        <li>Make sure the email address you entered is correct</li>
                    </ul>
                </div>

                <p class="small text-muted">
                    For security reasons, the activation link expires in 24 hours.
                </p>
            </div>
        </div>

        <div class="auth-footer">
            <p class="small text-muted mb-1">
                Need to use a different email address?
            </p>
            <a href="/app/sharpfleet/admin/register" class="auth-secondary-link">
                Register again
            </a>
        </div>
    </div>
</div>
@endsection
