{{--
  Page: customers/set-password.blade.php (assuming path)
  Version: v1.2 (Navy Success Text)
  Description: Updated success alert text to navy (#0A2A4D) for better brand alignment on light green bg.
  - Inline style tweak only; preserves all set-password functionality (submission to password.store, CSRF, error/status sessions, strength calculation unchanged).
  Last updated: 09 Nov 2025 by Grok
--}}
@extends('layouts.base')
@section('title', 'Set Your Password')
@section('content')
<section class="hero" style="padding-top: 2rem; min-height: auto;">
  <!-- CPU background for thematic consistency with home/registration/login -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- Hero logo only -->
  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo" style="margin-bottom: -1rem;">

  <!-- Single centered/wider card directly under logo; holds full set-password form -->
  <div class="hero-cards fade-section" style="justify-content: center; margin-top: 0;">
    <div class="tile transparent single-reg-card" style="width: 500px; max-width: 90%; flex: none; padding: 2.5rem;">
      <!-- Title -->
      <h1 style="color: white; text-align: center; margin-bottom: 1rem;">Set Your Password</h1>

      <!-- Success / error messages (navy text on success alert for brand match) -->
      @if(session('status'))
        <div class="alert alert-success" style="color: #0A2A4D; background-color: rgba(216, 243, 220, 0.9); border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;">
          {{ session('status') }}
        </div>
      @endif
      @if($errors->any())
        <div class="alert alert-error" style="color: white; background-color: rgba(255, 227, 227, 0.9); border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;">
          @foreach ($errors->all() as $error)
            <p style="color: white; margin: 0;">{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <!-- Set-password form with white text overrides -->
      <form method="POST" action="{{ route('password.store', ['id' => $user->id]) }}" class="onboard-form" style="color: white;">
        @csrf
        <div class="form-group">
          <label for="password" style="color: white; font-weight: 600;">New Password</label>
          <input id="password" name="password" type="password" required style="color: white; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
          <div id="password-strength" class="password-strength" style="margin-top: 0.5rem;">
            <div class="strength-bar" style="height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; overflow: hidden;">
              <div class="strength-fill" style="height: 100%; width: 0%; transition: width 0.3s ease; border-radius: 2px;"></div>
            </div>
            <span id="strength-text" style="display: block; margin-top: 0.25rem; font-size: 0.875rem; color: rgba(255,255,255,0.7);">Enter a password</span>
          </div>
        </div>
        <div class="form-group">
          <label for="password_confirmation" style="color: white; font-weight: 600;">Confirm Password</label>
          <input id="password_confirmation" name="password_confirmation" type="password" required style="color: white; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
        </div>
        <button type="submit" class="btn-primary w-full" style="background-color: #2CBFAE; color: white;">Save Password</button>
      </form>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const passwordInput = document.getElementById('password');
  const strengthBar = document.querySelector('.strength-fill');
  const strengthText = document.getElementById('strength-text');
  function calculateStrength(password) {
    let score = 0;
    if (password.length >= 8) score += 1;
    if (/[a-z]/.test(password)) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/\d/.test(password)) score += 1;
    if (/[^a-zA-Z\d]/.test(password)) score += 1;
    return score;
  }
  function updateStrength(password) {
    const score = calculateStrength(password);
    let width = 0, color = 'rgba(255,255,255,0.2)', text = 'Weak';
    if (score <= 2) {
      width = 33; color = '#ff4d4d'; text = 'Weak';
    } else if (score <= 3) {
      width = 66; color = '#ffaa00'; text = 'Medium';
    } else {
      width = 100; color = '#4CAF50'; text = 'Strong';
    }
    strengthBar.style.width = width + '%';
    strengthBar.style.backgroundColor = color;
    strengthText.textContent = text;
    strengthText.style.color = color;
  }
  passwordInput.addEventListener('input', function() {
    updateStrength(this.value);
  });
  // Initial state
  updateStrength('');
});
</script>
@endsection