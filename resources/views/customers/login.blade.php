{{--  
  Page: customers/login.blade.php  
  Version: FINAL (Login 2FA Working) 
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

      {{-- ERROR MESSAGE --}}
      @if (session('error'))
        <div class="alert alert-error"
             style="color: #0A2A4D; background-color: rgba(255,227,227,0.9);
                    border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem;">
          {{ session('error') }}
        </div>
      @endif

      {{-- STATUS MESSAGE --}}
      @if (session('status'))
        <div class="alert alert-success"
             style="color: #0A2A4D; background-color: rgba(216,243,220,0.95);
                    border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem;
                    font-weight: 600; text-align:center;">
          {{ session('status') }}
        </div>
      @endif


      {{-- LOGIN FORM --}}
      <form action="{{ route('customer.login.submit') }}" method="POST" class="onboard-form" style="color: white;">
        @csrf

        <div class="form-group">
          <label for="email" style="color: white; font-weight: 600;">Email Address</label>
          <input type="email" id="email" name="email" required
                 value="{{ old('email') }}"
                 placeholder="you@example.com"
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem; width: 100%;">
        </div>

        <div class="form-group">
          <label for="password" style="color: white; font-weight: 600;">Password</label>
          <input type="password" id="password" name="password" required
                 placeholder="Enter your password"
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem; width: 100%;">
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 1rem;">
          <input type="checkbox" name="remember" id="remember"
                 style="width: 18px; height: 18px; accent-color: #2CBFAE;">
          <label for="remember" style="color: white; font-weight: 600;">Remember me</label>
        </div>

        <div style="text-align: right; margin-bottom: 1.5rem;">
          <a href="{{ route('customer.password.request') }}" style="color: #2CBFAE; font-weight: 500;">
            Forgot your password?
          </a>
        </div>

        <button type="submit" class="btn-primary w-full"
                style="background-color: #2CBFAE; color: white; padding: 0.75rem;
                       width: 100%; border-radius: 8px; font-weight: 600;">
          Log In
        </button>
      </form>

      <p class="onboard-note" style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 1.5rem;">
        Don’t have an account yet?
        <a href="{{ route('register') }}" style="color: #2CBFAE; font-weight: 500;">Create one here</a>
      </p>

    </div>
  </div>

</section>


{{-- ================================================================= --}}
{{-- LOGIN-TIME 2FA MODAL --}}
{{-- ================================================================= --}}
@if(session('show_2fa_modal'))
<div id="login-2fa-backdrop" class="cp-modal-backdrop cp-modal-visible">

    <div class="cp-modal-sheet">
        <header style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0; font-size:1.2rem;">Two-Factor Authentication</h3>
                <p style="margin:0; font-size:0.9rem; color:#555;">
                    Enter the code sent to <strong>{{ session('email_masked') }}</strong>
                </p>
            </div>
            <button onclick="window.location.reload()" 
                    style="border:none; background:none; font-size:1.4rem; cursor:pointer;">&times;</button>
        </header>

        <div style="margin-top:1rem;">

            {{-- OTP INPUTS --}}
            <div style="display:flex; gap:8px; justify-content:center; margin-bottom:1rem;">
                @for($i = 1; $i <= 6; $i++)
                    <input maxlength="1"
                           class="login-2fa-digit"
                           style="width:45px; height:55px; text-align:center;
                                  font-size:1.6rem; border-radius:10px;
                                  border:1px solid #ccc;">
                @endfor
            </div>

            {{-- ERROR --}}
            <div id="login-2fa-error"
                 style="color:#b00020; margin:0.5rem 0; text-align:center; display:none;">
            </div>

            <button id="login-2fa-submit"
                    class="cp-btn cp-teal-btn"
                    style="width:100%; margin-top:0.5rem;"
                    disabled>
                Verify Code
            </button>

            <button id="login-2fa-resend"
                    class="cp-btn cp-navy-btn"
                    style="width:100%; margin-top:0.5rem;">
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

    const digits = document.querySelectorAll('.login-2fa-digit');
    const submitBtn = document.getElementById('login-2fa-submit');
    const resendBtn = document.getElementById('login-2fa-resend');
    const errorBox  = document.getElementById('login-2fa-error');

    if (!digits.length) return;

    digits.forEach((input, index) => {
        input.addEventListener('input', () => {
            if (!/^[0-9]$/.test(input.value)) {
                input.value = '';
                return;
            }
            if (index < 5) digits[index + 1].focus();
            checkReady();
        });
    });

    function checkReady() {
        const code = [...digits].map(i => i.value).join('');
        submitBtn.disabled = code.length !== 6;
    }

    // VERIFY 2FA
    submitBtn?.addEventListener('click', function(){
        const code = [...digits].map(i => i.value).join('');

        fetch("{{ route('customer.security.email.verify-login-code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ code })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                window.location = res.redirect;
            } else {
                errorBox.innerText = res.message;
                errorBox.style.display = 'block';
                digits.forEach(i => i.value = '');
                digits[0].focus();
                submitBtn.disabled = true;
            }
        });
    });

    // RESEND
    resendBtn?.addEventListener('click', function(){
        fetch("{{ route('customer.security.email.send-login-code') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        });
    });

})();
</script>
@endpush
