{{--  
  Page: customers/login.blade.php  
  Version: FINAL (Login-Time 2FA Integration + Debug) 
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
             style="width: 80px; height: auto; filter: drop-shadow(0 0 4px rgba(44,191,174,0.3));">
      </div>

      <h1 class="onboard-title"
          style="color: white; margin-bottom: 0.5rem; text-align: center;">
        Sign In to Your Account
      </h1>

      {{-- Errors --}}
      @if (session('error'))
        <div class="alert alert-error"
             style="color: white; background-color: rgba(255, 227, 227, 0.9);
                    border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;
                    border-radius: 6px; padding: 0.75rem;">
          {{ session('error') }}
        </div>
      @endif

      {{-- Status --}}
      @if (session('status'))
        <div class="alert alert-success"
             style="display: flex; align-items: center; justify-content: center;
                    gap: 0.5rem; color: #0A2A4D; font-weight: 600; text-align: center;
                    background-color: rgba(216, 243, 220, 0.95);
                    border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1rem;
                    padding: 0.75rem 1rem; border-radius: 6px;">
          ✔ {{ session('status') }}
        </div>
      @endif

      {{-- LOGIN FORM --}}
      <form action="{{ route('customer.login.submit') }}" method="POST"
            class="onboard-form" style="color: white;">
        @csrf

        <div class="form-group">
          <label for="email" style="color: white; font-weight: 600;">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="you@example.com"
                 value="{{ old('email') }}"
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem; width: 100%;">
        </div>

        <div class="form-group">
          <label for="password" style="color: white; font-weight: 600;">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter your password"
                 style="color: white; background-color: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.25);
                        border-radius: 6px; padding: 0.6rem; width: 100%;">
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 1rem;">
          <input type="checkbox" name="remember" id="remember"
                 style="width: 18px; height: 18px; accent-color: #2CBFAE;">
          <label for="remember" style="color: white; font-weight: 600; margin: 0;">
            Remember me
          </label>
        </div>

        <div style="text-align: right; margin-bottom: 1.5rem;">
          <a href="{{ route('customer.password.request') }}" 
             style="color: #2CBFAE; font-weight: 500;">
            Forgot your password?
          </a>
        </div>

        <button type="submit" class="btn-primary w-full"
                style="background-color: #2CBFAE; color: white; padding: 0.75rem;
                       width: 100%; border-radius: 8px; font-weight: 600;">
          Log In
        </button>
      </form>

      <p class="onboard-note"
         style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 1.5rem;">
        Don’t have an account yet?
        <a href="{{ route('register') }}" style="color: #2CBFAE; font-weight: 500;">
          Create one here
        </a>
      </p>

    </div>
  </div>

</section>
Max, generate the updated JS + OTP HTML
@endsection
@push('scripts')
<script>
(function(){

    const digits      = [...document.querySelectorAll('.otp-digit')];
    const submitBtn   = document.getElementById('login-2fa-submit');
    const resendBtn   = document.getElementById('login-2fa-resend');
    const errorBox    = document.getElementById('login-2fa-error');
    const modalSheet  = document.querySelector('.cp-modal-sheet');
    const closeBtn    = document.getElementById('cp-close-2fa');

    const backdrop    = document.getElementById('login-2fa-backdrop');
    const autofill    = document.getElementById('otp-autofill');

    if (!digits.length) return;

    /* ================================ */
    /*  Close Modal (FIXED)             */
    /* ================================ */
    closeBtn?.addEventListener('click', () => {
        backdrop?.classList.remove('cp-modal-visible');
    });

    /* ==================================================== */
    /*  MOBILE AUTOFILL (Gmail / SMS Suggestion Capture)    */
    /* ==================================================== */
    autofill?.addEventListener('input', (e) => {
        let code = e.target.value.replace(/\D/g, '').slice(0, 6);
        if (code.length === 6) {
            digits.forEach((box, i) => box.value = code[i]);
            submitBtn.disabled = false;
        }
    });

    /* ================================ */
    /*  Auto-advance + Paste Logic      */
    /* ================================ */
    digits.forEach((input, idx) => {

        input.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '');
            e.target.value = val;

            if (val && idx < 5) digits[idx + 1].focus();

            checkReady();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                digits[idx - 1].focus();
            }
        });

        /* Full 6-digit paste */
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            let paste = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6)
                .split('');

            digits.forEach((box, i) => { box.value = paste[i] || '' });
            checkReady();
        });
    });

    function getCode() {
        return digits.map(i => i.value).join('');
    }

    function checkReady() {
        submitBtn.disabled = (getCode().length !== 6);
    }

    function showError(msg) {
        errorBox.innerText = msg;
        errorBox.style.display = 'block';

        modalSheet.classList.remove('shake');
        void modalSheet.offsetWidth;
        modalSheet.classList.add('shake');
    }

    /* ================================ */
    /*  VERIFY CODE                     */
    /* ================================ */
    submitBtn.addEventListener('click', function(){

        const code = getCode();
        if (code.length !== 6) return;

        // Loading dots
        submitBtn.classList.add('loading-dots');
        submitBtn.innerText = "Verifying";
        submitBtn.disabled  = true;

        errorBox.style.display = 'none';

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
                return;
            }

            // Wrong code
            digits.forEach(i => i.value = '');
            digits[0].focus();
            showError(res.message);

            // Undo loader
            submitBtn.classList.remove('loading-dots');
            submitBtn.innerText = "Verify Code";
            submitBtn.disabled = true;
        });
    });

    /* ================================ */
    /*  RESEND CODE                     */
    /* ================================ */
    resendBtn.addEventListener('click', () => {
        fetch("{{ route('customer.security.email.send-login-code') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        });
    });

})();
</script>
@endpush
