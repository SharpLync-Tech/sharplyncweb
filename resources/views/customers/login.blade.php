{{--  
  Page: customers/login.blade.php  
  Version: v2.1 (Polished Visual Refresh + UX Enhancements)  
  Description:  
  - Removed icon filter to preserve brand teal color.  
  - Added "Forgot Password" link (functionality to be implemented later).  
  - Improved spacing, shadows, field focus styling, and button hover transitions.  
  - Adjusted for perfect alignment + responsive layout on mobile.  
  - No functionality changes; routes, CSRF, and session handling fully intact.  
  Last updated: 11 Nov 2025 by Jannie & Max  
--}}

@extends('layouts.base')
@section('title', 'Customer Login')
@section('content')
<section class="hero" style="padding-top: 4rem; min-height: auto;">
  <!-- CPU background for thematic consistency -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- Hero logo -->
  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo"
       class="hero-logo" style="margin-bottom: -1rem;">

  <!-- Centered login card -->
  <div class="hero-cards fade-section" style="justify-content: center; margin-top: 0;">
    <div class="tile transparent single-reg-card"
         style="width: 500px; max-width: 90%; flex: none; padding: 2.5rem;
                background: rgba(10, 42, 77, 0.85);
                backdrop-filter: blur(6px);
                border-radius: 16px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.25);">

      <!-- Lock icon -->
      <div class="onboard-icon" style="text-align: center; margin-bottom: 1rem;">
        <img src="{{ asset('images/login-lock.png') }}" alt="Login Icon"
             style="width: 80px; height: auto; filter: drop-shadow(0 0 4px rgba(44,191,174,0.3));">
      </div>

      <!-- Title + Subtitle -->
      <h1 class="onboard-title"
          style="color: white; margin-bottom: 0.5rem; text-align: center;">
        Welcome Back
      </h1>
      <p class="onboard-subtitle"
         style="color: rgba(255,255,255,0.8); text-align: center; margin-bottom: 1.5rem;">
        Log in to your SharpLync account to continue where you left off.
      </p>

      <!-- Alerts -->
      @if (session('error'))
        <div class="alert alert-error"
             style="color: white; background-color: rgba(255, 227, 227, 0.9);
                    border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;
                    border-radius: 6px; padding: 0.75rem;">
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

      <!-- Login form -->
      <form action="{{ route('customer.login.submit') }}" method="POST"
            class="onboard-form" style="color: white;">
        @csrf

        <div class="form-group">
          <label for="email" style="color: white; font-weight: 600;">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="you@example.com"
                 value="{{ old('email') }}"
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem;
                        transition: all 0.2s ease; width: 100%;">
        </div>

        <div class="form-group">
          <label for="password" style="color: white; font-weight: 600;">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter your password"
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem;
                        transition: all 0.2s ease; width: 100%;">
        </div>

        <!-- Remember me -->
        <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 1rem;">
          <input type="checkbox" name="remember" id="remember"
                 style="width: 18px; height: 18px; accent-color: #2CBFAE; cursor: pointer;">
          <label for="remember" style="color: white; font-weight: 600; cursor: pointer; margin: 0;">
            Remember me
          </label>
        </div>

        <!-- Forgot password link -->
        <div style="text-align: right; margin-bottom: 1.5rem;">
          <a href="#" style="color: #2CBFAE; font-weight: 500; text-decoration: none;">
            Forgot your password?
          </a>
        </div>

        <!-- Login button -->
        <button type="submit" class="btn-primary w-full"
                style="background-color: #2CBFAE; color: white; padding: 0.75rem;
                       width: 100%; border-radius: 8px; font-weight: 600;
                       transition: all 0.2s ease-in-out;">
          Log In
        </button>
      </form>

      <!-- Registration link -->
      <p class="onboard-note"
         style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 1.5rem;">
        Don’t have an account yet?
        <a href="{{ route('register') }}" style="color: #2CBFAE; text-decoration: none; font-weight: 500;">
          Create one here
        </a>
      </p>
    </div>
  </div>

  <!-- Mobile optimization -->
  <style>
    @media (max-width: 768px) {
      .hero-cpu-bg img {
        opacity: 0.25;
        transform: scale(1.2);
        right: 0;
      }
      .single-reg-card {
        padding: 2rem 1.5rem !important;
        width: 95% !important;
      }
      .hero-logo {
        width: 200px;
      }
    }

    input:focus {
      border-color: #2CBFAE !important;
      background-color: rgba(255,255,255,0.15) !important;
      outline: none;
    }

    .btn-primary:hover {
      background-color: #25ab9d !important;
      transform: translateY(-2px);
    }
  </style>
</section>
@endsection