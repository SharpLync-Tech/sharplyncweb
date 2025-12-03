{{--  
  Page: customers/register.blade.php  
  Version: v3.6 (OAuth Buttons Removed)  
  Description: Removed Google and Microsoft login actions for streamlined email-only registration.  
  Last updated: 11 Nov 2025 by Max  
--}}
<style>
    .hero-cpu-bg {
        display: none !important;
    }
</style>

@extends('layouts.base')
@section('title', 'Create Your SharpLync Account')
@section('content')
<section class="hero" style="padding-top: 2rem; min-height: auto;">
  <!-- CPU background for thematic consistency with home page -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- Hero logo only (intro text removed/moved out) -->
  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo" style="margin-bottom: -1rem;">

  <!-- Single centered/wider card directly under logo; holds full registration form -->
  <div class="hero-cards fade-section" style="justify-content: center; margin-top: 0;">
    <div class="tile transparent single-reg-card" style="width: 500px; max-width: 90%; flex: none; padding: 2.5rem;">
      <!-- Success / error messages (navy text on success alert for brand match) -->
      @if(session('status'))
        <div class="alert alert-success" style="color: #0A2A4D; background-color: rgba(216, 243, 220, 0.9); border: 1px solid rgba(255,255,255,0.2);">{{ session('status') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-error" style="color: white; background-color: rgba(255, 227, 227, 0.9); border: 1px solid rgba(255,255,255,0.2);">
          @foreach ($errors->all() as $error)
            <p style="color: white; margin: 0;">{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <!-- Registration form with white text overrides -->
      <form method="POST" action="{{ route('register.submit') }}" class="onboard-form" style="color: white;">
        @csrf
        <div class="form-group">
          <label for="first_name" style="color: white; font-weight: 600;">First Name</label>
          <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required style="color: white; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
        </div>
        <div class="form-group">
          <label for="last_name" style="color: white; font-weight: 600;">Last Name</label>
          <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required style="color: white; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
        </div>
        <div class="form-group">
          <label for="email" style="color: white; font-weight: 600;">Email Address</label>
          <input type="email" name="email" id="email" value="{{ old('email') }}" required style="color: white; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
        </div>
        <button type="submit" class="btn-primary w-full" style="background-color: #2CBFAE; color: white;">Send Verification Email</button>
      </form>
    </div>
  </div>
</section>
@endsection