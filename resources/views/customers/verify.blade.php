{{-- 
  Page: customers/verify.blade.php
  Version: v2.1 (Clean markup, class-based)
  Description: Step 2 – Shown after user clicks the verification link from their email
--}}

@extends('layouts.base')

@section('title', 'Email Verified – Set Your Password')

@section('content')
<section class="onboard-container">
  <div class="onboard-card text-center">

    <div class="onboard-icon success">
      <img src="{{ asset('images/icons/success-check.svg') }}" alt="Success">
    </div>

    <h1>Email Verified!</h1>
    <p class="onboard-subtitle">
      Your SharpLync account has been successfully verified.<br>
      Let’s secure it by setting your password.
    </p>

    <a href="{{ route('password.create', ['id' => $userId ?? 0]) }}" class="btn-primary w-full">
      Continue to Set Password
    </a>

    <p class="onboard-note">If this page doesn’t redirect, click the button above to continue.</p>

  </div>
</section>
@endsection