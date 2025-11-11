{{--
  Page: customers/login.blade.php
  Version: v1.1 (Hero-Style Single Card)
  Description: Adapted to match registration's hero layout: CPU bg, logo, compact single card under logo with white text.
  - Inline overrides for dark bg readability; icon/title/subtitle integrated into card.
  - Preserves all login functionality (submission to customer.login.submit, CSRF, old() repopulation, remember checkbox, error/status sessions, OAuth redirects, register link unchanged).
  Last updated: 09 Nov 2025 by Grok
--}}
@extends('layouts.base')
@section('title', 'Customer Login')
@section('content')
<section class="hero" style="padding-top: 2rem; min-height: auto;">
  <!-- CPU background for thematic consistency with home/registration -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- Hero logo only -->
  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo" style="margin-bottom: -1rem;">

  <!-- Single centered/wider card directly under logo; holds full login form -->
  <div class="hero-cards fade-section" style="justify-content: center; margin-top: 0;">
    <div class="tile transparent single-reg-card" style="width: 500px; max-width: 90%; flex: none; padding: 2.5rem;">
      <!-- Login icon, title, subtitle -->
      <div class="onboard-icon" style="text-align: center; margin-bottom: 1rem;">
        <img src="{{ asset('images/icons/login-lock.svg') }}" alt="Login Icon" style="width: 80px; height: auto; filter: brightness(0) invert(1);">
      </div>
      <h1 class="onboard-title" style="color: white; margin-bottom: 0.5rem; text-align: center;">Welcome Back</h1>
      <p class="onboard-subtitle" style="color: rgba(255,255,255,0.8); text-align: center; margin-bottom: 1.5rem;">Log in to your SharpLync account to continue where you left off.</p>

      <!-- Success / error messages (white text via inline for dark bg) -->
      @if (session('error'))
        <div class="alert alert-error" style="color: white; background-color: rgba(255, 227, 227, 0.9); border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;">
          {{ session('error') }}
        </div>
      @endif
      @if (session('status'))
        <div class="alert alert-success" style="color: white; background-color: rgba(216, 243, 220, 0.9); border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;">
          {{ session('status') }}
        </div>
      @endif

      <!-- Login form with white text overrides -->
      <form action="{{ route('customer.login.submit') }}" method="POST" class="onboard-form" style="color: white;">
        @csrf
        <div class="form-group">
          <label for="email" style="color: white; font-weight: 600;">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="you@example.com" value="{{ old('email') }}" style="color: white; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
        </div>
        <div class="form-group">
          <label for="password" style="color: white; font-weight: 600;">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter your password" style="color: white; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
        </div>
        <div class="form-group">
          <label style="color: white; font-weight: 600;">
            <input type="checkbox" name="remember" style="margin-right: 5px; accent-color: #2CBFAE;"> Remember me
          </label>
        </div>
        <button type="submit" class="btn-primary w-full" style="background-color: #2CBFAE; color: white;">Log In</button>
      </form>

      <div class="onboard-divider" style="color: rgba(255,255,255,0.8);">or</div>
      <div class="oauth-buttons">
        <a href="{{ route('customer.google.redirect') }}" class="btn-oauth google">Continue with Google</a>
        <a href="{{ route('customer.microsoft.redirect') }}" class="btn-oauth microsoft">Continue with Microsoft</a>
      </div>

      <p class="onboard-note" style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 1.5rem;">
        Don’t have an account yet?
        <a href="{{ route('register') }}" style="color: #2CBFAE;">Create one here</a>
      </p>
    </div>
  </div>
</section>
@endsection