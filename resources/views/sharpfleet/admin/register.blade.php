@extends('layouts.sharpfleet')

@section('title', 'Register for SharpFleet')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Start Your SharpFleet Trial</h1>
            <p class="auth-subtitle">30 days free • No credit card required</p>
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

        <form method="POST" action="/app/sharpfleet/admin/register" class="auth-form">
            @csrf

            {{-- Personal Details --}}
            <div class="form-section">
                <h3 class="section-title">Your Details</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control"
                               value="{{ old('first_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control"
                               value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email') }}" required>
                    <div class="form-hint">This will be your login email</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full mt-2">
                Send Activation Email
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="/app/sharpfleet/login">Sign in</a></p>
        </div>
    </div>
</div>
@endsection
