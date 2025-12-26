@extends('layouts.sharpfleet')

@section('title', 'Check Your Email - SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Check Your Email</h1>
            <p class="auth-subtitle">We've sent you an activation link</p>
        </div>

        <div class="auth-content">
            <div class="success-message">
                <div class="success-icon">📧</div>
                <h3>Registration Successful!</h3>
                <p>We've sent an activation email to your email address. Please check your inbox and click the activation link to complete your registration.</p>

                <div class="info-box">
                    <p><strong>Didn't receive the email?</strong></p>
                    <ul>
                        <li>Check your spam/junk folder</li>
                        <li>The email may take a few minutes to arrive</li>
                        <li>Make sure you entered the correct email address</li>
                    </ul>
                </div>

                <p>The activation link will expire in 24 hours for security reasons.</p>
            </div>
        </div>

        <div class="auth-footer">
            <p><a href="/app/sharpfleet/admin/register">← Back to Registration</a></p>
        </div>
    </div>
</div>

@endsection