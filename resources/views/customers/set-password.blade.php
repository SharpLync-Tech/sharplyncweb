@extends('layouts.base')
@section('title', 'Set Your Password')

@section('content')
<section class="hero" style="padding-top: 2rem; min-height: auto;">
  <!-- CPU background -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- Logo -->
  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo"
       class="hero-logo" style="margin-bottom: -1rem;">

  <!-- Main Card -->
  <div class="hero-cards fade-section" style="justify-content: center; margin-top: 0;">
    <div class="tile transparent single-reg-card"
         style="width: 500px; max-width: 90%; flex: none; padding: 2.5rem;">

      <h1 style="color: white; text-align: center; margin-bottom: 1rem;">Set Your Password</h1>

      <!-- Success -->
      @if(session('status'))
        <div style="color: #0A2A4D; background-color: rgba(216, 243, 220, 0.9);
                    border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem; padding:1rem;">
          {{ session('status') }}
        </div>
      @endif

      <!-- Errors -->
      @if($errors->any())
        <div style="color: white; background-color: rgba(255, 227, 227, 0.9);
                    border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem; padding:1rem;">
          @foreach ($errors->all() as $error)
            <p style="margin: 0;">{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <!-- 🔥 FORM -->
      <form method="POST" action="{{ route('password.store', ['id' => $user->id]) }}"
            class="onboard-form" style="color: white;">
        @csrf

        <!-- New reusable component -->
        <x-password-field
            label="New Password"
            name="password"
            confirm="password_confirmation"
            show-generator="true"
            show-strength="true"
        />

        <button type="submit"
                style="width:100%; padding:0.75rem; background:#2CBFAE;
                       border-radius:8px; color:white; font-weight:600;">
          Save Password
        </button>
      </form>
    </div>
  </div>
</section>
@endsection
