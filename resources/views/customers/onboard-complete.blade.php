{{-- 
  Page: customers/onboard-complete.blade.php
  Version: v2.1 (Final onboarding step)
  Description: Confirmation page shown when customer setup is complete
--}}

@extends('layouts.base')

@section('title', 'Welcome to SharpLync')

@section('content')
<section class="onboard-container">
  <div class="onboard-card text-center">

    <div class="onboard-icon success">
      <img src="{{ asset('images/icons/confetti.svg') }}" alt="Success">
    </div>

    <h1>Welcome to SharpLync!</h1>
    <p class="onboard-subtitle">
      Your account is fully set up and ready to go.<br>
      You’re now part of the SharpLync community — where real people get IT done right.
    </p>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ url('/dashboard') }}" class="btn-primary w-full">
      Go to Dashboard
    </a>

    <p class="onboard-note">
      Need help? <a href="{{ url('/contact') }}">Contact Support</a>
    </p>

  </div>
</section>
@endsection