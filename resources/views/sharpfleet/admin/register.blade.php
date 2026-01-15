@extends('layouts.sharpfleet')

@section('title', 'Register for SharpFleet')

@section('sharpfleet-content')
<div class="auth-container sharpfleet-register">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <img src="{{ asset('images/login-lock.png') }}" alt="Register">
            </div>
            <h1 class="auth-title">Start Your SharpFleet Trial</h1>
            <p class="auth-subtitle">30 days free • No credit card required</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/app/sharpfleet/admin/register" class="auth-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                <div class="form-hint">This will be your login email</div>
            </div>

            <div class="form-group" style="margin-top: 12px;">
                <label class="checkbox-label" for="terms_agree">
                    <input type="checkbox" id="terms_agree" name="terms_agree" required>
                    I have read and agree to the
                    <a href="https://sharplync.com.au/policies/sharpfleet-terms" target="_blank" rel="noopener">
                        Terms &amp; Conditions
                    </a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full mt-2" id="activationSubmit" disabled>
                Send Activation Email
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="/app/sharpfleet/login">Sign in</a></p>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const termsCheckbox = document.getElementById('terms_agree');
    const submitButton = document.getElementById('activationSubmit');
    if (!termsCheckbox || !submitButton) return;

    const syncState = () => {
        submitButton.disabled = !termsCheckbox.checked;
    };

    termsCheckbox.addEventListener('change', syncState);
    syncState();
});
</script>
@endsection
