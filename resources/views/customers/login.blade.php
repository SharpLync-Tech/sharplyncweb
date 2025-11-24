{{--  
  Page: customers/login.blade.php  
  Version: Stable + Close Button Fix  
--}}

@extends('layouts.base')
@section('title', 'Customer Login')
@section('content')

<section class="hero" style="padding-top: 4rem; min-height: auto;">

  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo"
       class="hero-logo" style="margin-bottom: -1rem;">

  <div class="hero-cards fade-section" style="justify-content: center; margin-top: 0;">
    <div class="tile transparent single-reg-card"
         style="width: 500px; max-width: 90%; flex: none; padding: 2.5rem;
                background: rgba(10, 42, 77, 0.85);
                backdrop-filter: blur(6px);
                border-radius: 16px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.25);">

      <div class="onboard-icon" style="text-align: center; margin-bottom: 1rem;">
        <img src="{{ asset('images/login-lock.png') }}" alt="Login Icon"
             style="width: 80px; height: auto;">
      </div>

      <h1 class="onboard-title"
          style="color: white; margin-bottom: 0.5rem; text-align: center;">
        Sign In to Your Account
      </h1>

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
             style="color: #0A2A4D; background-color: rgba(216, 243, 220, 0.95); margin-bottom: 1rem;">
          ✔ {{ session('status') }}
        </div>
      @endif

      <form action="{{ route('customer.login.submit') }}" method="POST"
            class="onboard-form" style="color: white;">
        @csrf

        <div class="form-group">
          <label style="color: white; font-weight: 600;">Email Address</label>
          <input type="email" name="email" required value="{{ old('email') }}"
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem; width: 100%;">
        </div>

        <div class="form-group">
          <label style="color: white; font-weight: 600;">Password</label>
          <input type="password" name="password" required
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem; width: 100%;">
        </div>

        <button type="submit"
                style="background-color: #2CBFAE; color: white; padding: 0.75rem;
                       width: 100%; border-radius: 8px; font-weight: 600;">
          Log In
        </button>
      </form>

    </div>
  </div>
</section>


{{-- ========================================================== --}}
{{--             LOGIN-TIME 2FA MODAL (UNCHANGED)               --}}
{{-- ========================================================== --}}
@if(session('show_2fa_modal'))
<div id="login-2fa-backdrop" class="cp-modal-backdrop cp-modal-visible">

    <div class="cp-modal-sheet" style="max-width:460px; position:relative;">

        <header class="cp-modal-header">
            <div>
                <h3>Two-Factor Authentication</h3>
                <p class="cp-modal-subtitle">
                    Enter the code sent to <strong>{{ session('email_masked') }}</strong>
                </p>
            </div>

            {{-- FIXED CLOSE BUTTON --}}
            <button id="cp-close-2fa" class="cp-modal-close" 
                style="position:absolute; right:12px; top:12px;">✕</button>
        </header>

        <div class="cp-modal-body">

            <div style="display:flex; gap:8px; justify-content:center;">
                @for($i = 1; $i <= 6; $i++)
                    <input maxlength="1"
                           class="login-2fa-digit"
                           style="width:45px; height:55px; text-align:center;
                                  font-size:1.6rem; border-radius:10px;
                                  border:1px solid #ccc;">
                @endfor
            </div>

            <div id="login-2fa-error"
                 style="color:#b00020; margin-top:1rem; text-align:center; display:none;">
            </div>

            <button id="login-2fa-submit"
                    class="cp-btn cp-teal-btn"
                    style="margin-top:1.5rem; width:100%;"
                    disabled>
                Verify Code
            </button>

            <button id="login-2fa-resend"
                    class="cp-btn cp-navy-btn"
                    style="margin-top:0.75rem; width:100%;">
                Resend Code
            </button>

        </div>
    </div>
</div>
@endif

@endsection


@push('scripts')
<script>
(function(){

    const digits   = [...document.querySelectorAll('.login-2fa-digit')];
    const submit   = document.getElementById('login-2fa-submit');
    const resend   = document.getElementById('login-2fa-resend');
    const errorBox = document.getElementById('login-2fa-error');
    const closeBtn = document.getElementById('cp-close-2fa');
    const modalBg  = document.getElementById('login-2fa-backdrop');

    /* === FIX CLOSE BUTTON === */
    closeBtn?.addEventListener('click', () => {
        modalBg?.classList.remove('cp-modal-visible');
    });

    if (!digits.length) return;

    /* OTP input auto-advance */
    digits.forEach((box, idx) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '');
            if (box.value && idx < 5) digits[idx + 1].focus();

            submit.disabled = digits.map(i => i.value).join('').length !== 6;
        });
    });

})();
</script>
@endpush
