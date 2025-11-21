{{--  
  Page: customers/set-password.blade.php  
  Version: v2.0 (Enhanced UX: Eye Toggle + Match Indicator + Generator + Unified Strength Meter)  
  Last updated: 22 Nov 2025 by Max  
--}}

@extends('layouts.base')
@section('title', 'Set Your Password')

@section('content')
<section class="hero" style="padding-top: 2rem; min-height: auto;">
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <img src="{{ asset('images/sharplync-logo.png') }}" class="hero-logo" style="margin-bottom: -1rem;">

  <div class="hero-cards fade-section" style="justify-content: center; margin-top: 0;">
    <div class="tile transparent single-reg-card"
         style="width: 500px; max-width: 90%; padding: 2.5rem; border-radius: 16px;
                background: rgba(10,42,77,0.85); backdrop-filter: blur(6px);
                box-shadow: 0 8px 24px rgba(0,0,0,0.25);">

      <h1 style="color: white; text-align: center; margin-bottom: 1rem;">Set Your Password</h1>

      @if(session('status'))
        <div style="color:#0A2A4D; background-color:rgba(216,243,220,0.9);
                    border-radius:6px; padding:12px; margin-bottom:1rem;">
          {{ session('status') }}
        </div>
      @endif

      @if($errors->any())
        <div style="color:white; background-color:rgba(255,227,227,0.9);
                    border-radius:6px; padding:12px; margin-bottom:1rem;">
          @foreach ($errors->all() as $error)
            <p style="margin:0;">{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <!-- FORM -->
      <form method="POST" action="{{ route('password.store', ['id' => $user->id]) }}" style="color:white;">
        @csrf

        <!-- PASSWORD FIELD -->
        <div style="margin-bottom:1rem;">
          <label style="font-weight:600;">New Password</label>

          <div style="position:relative;">
            <input id="password" name="password" type="password" required
                   style="width:100%; padding:0.6rem 2.5rem 0.6rem 0.6rem; border-radius:6px;
                          background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.3);
                          color:white;">

            <!-- EYE TOGGLE (clean SVG) -->
            <span id="togglePassword"
              style="position:absolute; right:10px; top:50%; transform:translateY(-50%);
                     cursor:pointer; font-size:18px;">

              <!-- Eye open -->
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                   fill="none" stroke="#2CBFAE" stroke-width="2" stroke-linecap="round"
                   stroke-linejoin="round">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>

              <!-- Eye closed -->
              <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                   fill="none" stroke="#2CBFAE" stroke-width="2" stroke-linecap="round"
                   stroke-linejoin="round" style="display:none;">
                <path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7
                         1.18-2.07 2.68-3.94 4.44-5.5"/>
                <path d="M9.88 9.88A3 3 0 0114.12 14.12"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </span>
          </div>

          <!-- Strength bar -->
          <div style="margin-top:8px; height:8px; background:rgba(255,255,255,0.15); border-radius:6px;">
            <div id="passwordStrengthBar"
                 style="height:100%; width:0%; border-radius:6px; transition:width 0.25s;"></div>
          </div>

          <!-- Strength label -->
          <div id="passwordStrengthText"
               style="margin-top:6px; font-size:0.9rem; color:#ccc;">
            Enter a password
          </div>

          <!-- Generate Strong Password -->
          <button type="button" id="generatePasswordBtn"
              style="margin-top:8px; background:#104976; color:white; padding:0.45rem 0.7rem;
                     border:none; border-radius:6px; font-size:0.85rem; cursor:pointer;">
            Generate Strong Password
          </button>
        </div>

        <!-- CONFIRM FIELD -->
        <div style="margin-bottom:1.5rem;">
          <label style="font-weight:600;">Confirm Password</label>

          <input id="password_confirmation" name="password_confirmation" type="password" required
                 style="width:100%; padding:0.6rem; border-radius:6px;
                        background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.3);
                        color:white;">

          <div id="passwordMatchText" style="margin-top:6px; font-size:0.9rem;"></div>
        </div>

        <button type="submit"
                style="width:100%; padding:0.75rem; background:#2CBFAE;
                       border-radius:8px; color:white; font-weight:600;">
          Save Password
        </button>
      </form>

    </div>
  </div>
</section>

<!-- JS Enhancements -->
<script>
// ===== SHOW/HIDE PASSWORD =====
document.getElementById('togglePassword').addEventListener('click', function () {
  const pw = document.getElementById('password');
  const eye = document.getElementById('eyeIcon');
  const eyeOff = document.getElementById('eyeOffIcon');

  if (pw.type === "password") {
    pw.type = "text";
    eye.style.display = "none";
    eyeOff.style.display = "block";
  } else {
    pw.type = "password";
    eye.style.display = "block";
    eyeOff.style.display = "none";
  }
});

// ===== PASSWORD STRENGTH =====
document.getElementById('password').addEventListener('input', function() {
  const val = this.value;
  const bar = document.getElementById('passwordStrengthBar');
  const text = document.getElementById('passwordStrengthText');

  let strength = 0;
  if (val.length >= 8) strength++;
  if (/[A-Z]/.test(val)) strength++;
  if (/[0-9]/.test(val)) strength++;
  if (/[^A-Za-z0-9]/.test(val)) strength++;

  let width = (strength / 4) * 100;
  bar.style.width = width + "%";

  if (strength <= 1) {
    bar.style.background = "#ff4d4d";
    text.textContent = "Weak";
  } else if (strength === 2) {
    bar.style.background = "#ffcc00";
    text.textContent = "Okay";
  } else if (strength === 3) {
    bar.style.background = "#2CBFAE";
    text.textContent = "Strong";
  } else {
    bar.style.background = "#2CBFAE";
    text.textContent = "Very Strong";
  }
});

// ===== PASSWORD MATCH CHECK =====
document.getElementById('password_confirmation').addEventListener('input', function() {
  const pw = document.getElementById('password').value;
  const pw2 = this.value;
  const matchText = document.getElementById('passwordMatchText');

  if (!pw2) {
    matchText.textContent = "";
    return;
  }

  if (pw === pw2) {
    matchText.style.color = "#2CBFAE";
    matchText.textContent = "✔ Passwords match";
  } else {
    matchText.style.color = "#ff4d4d";
    matchText.textContent = "✖ Passwords do not match";
  }
});

// ===== GENERATE STRONG PASSWORD =====
document.getElementById('generatePasswordBtn').addEventListener('click', function () {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*?";
  let pass = "";
  for (let i = 0; i < 14; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }

  const pwField = document.getElementById('password');
  const pwConfirm = document.getElementById('password_confirmation');

  pwField.value = pass;
  pwConfirm.value = pass;

  pwField.dispatchEvent(new Event('input'));
  pwConfirm.dispatchEvent(new Event('input'));
});
</script>

@endsection
