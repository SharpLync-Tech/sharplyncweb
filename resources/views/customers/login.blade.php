{{--
  Page: customers/login.blade.php
  Version: v2.0 (Hero-Style Single Card)
  Description: Adapted to match registration's hero layout: CPU bg, logo, compact single card under logo with white text.
  - Inline overrides for dark bg readability; icon/title/subtitle integrated into card.
  - Preserves all login functionality (submission to customer.login.submit, CSRF, old() repopulation
  - remember checkbox, error/status sessions, Google & Microsoft Removed, register link unchanged).
  Last updated: 09 Nov 2025 by Jannie
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
        <img src="{{ asset('images/icons/login-lock.png') }}" alt="Login Icon" style="width: 80px; height: auto; filter: brightness(0) invert(1);">
      </div>
      <h1 class="onboard-title" style="color: white; margin-bottom: 0.5rem; text-align: center;">Welcome Back</h1>
      <p class="onboard-subtitle" style="color: rgba(255,255,255,0.8); text-align: center; margin-bottom: 1.5rem;">Log in to your SharpLync account to continue where you left off.</p>

      <!-- Success / error messages (white text via inline for dark bg) -->
        @if (session('error'))
          <div class="alert alert-error" 
              style="color: white; background-color: rgba(255, 227, 227, 0.9); 
                      border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;">
            {{ session('error') }}
          </div>
        @endif

        @if (session('status'))
          <div class="alert alert-success" 
              style="display: flex; align-items: center; justify-content: center; 
                      gap: 0.5rem; color: #0A2A4D; font-weight: 600; text-align: center; 
                      background-color: rgba(216, 243, 220, 0.95); 
                      border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem; 
                      padding: 0.75rem 1rem; border-radius: 6px;">
            
            <!-- Animated tick icon -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" 
                fill="none" stroke="#2CBFAE" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" 
                style="width: 22px; height: 22px; animation: tickFade 0.8s ease-in-out;">
              <path d="M20 6L9 17l-5-5"/>
            </svg>
            
            <span>{{ session('status') }}</span>
          </div>

          <style>
            @keyframes tickFade {
              0% { opacity: 0; transform: scale(0.6) rotate(-10deg); }
              60% { opacity: 1; transform: scale(1.05) rotate(3deg); }
              100% { opacity: 1; transform: scale(1) rotate(0deg); }
            }
          </style>
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

      <p class="onboard-note" style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 1.5rem;">
        Don’t have an account yet?
        <a href="{{ route('register') }}" style="color: #2CBFAE;">Create one here</a>
      </p>
    </div>
  </div>
</section>
@endsection