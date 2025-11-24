{{--  
  Page: customers/login.blade.php  
  Version: FINAL (Login-Time 2FA Integration + Debug + App 2FA) 
--}}

@extends('layouts.base')
@section('title', 'Customer Login')
@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/2fa.css') }}">
@endpush
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

{{-- ================================================================= --}}
{{--               LOGIN-TIME 2FA MODAL — EMAIL (EXISTING)             --}}
{{-- ================================================================= --}}
@if(session('show_2fa_modal'))
<div id="login-2fa-backdrop" class="cp-modal-backdrop cp-modal-visible">

    <div class="cp-modal-sheet" style="max-width:460px;">
        <header class="cp-modal-header">
            <div>
                <h3>Two-Factor Authentication</h3>
                <p class="cp-modal-subtitle">
                    Enter the code sent to <strong>{{ session('email_masked') }}</strong>
                </p>
            </div>
            <button class="cp-modal-close" id="login-2fa-close">&times;</button>
        </header>

        <div class="cp-modal-body">          
            

            <div style="display:flex; gap:8px; justify-content:center;">
                @for($i = 1; $i <= 6; $i++)
                    <input maxlength="6"
                           class="login-2fa-digit"
                           data-id="{{ $i }}"
                           inputmode="numeric"
                           autocomplete="one-time-code"
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

{{-- ================================================================= --}}
{{--           LOGIN-TIME 2FA MODAL — AUTHENTICATOR APP (NEW)          --}}
{{-- ================================================================= --}}
@if(session('show_app_2fa_modal'))
<div id="login-app2fa-backdrop" class="cp-modal-backdrop cp-modal-visible">

    <div class="cp-modal-sheet" style="max-width:460px;">
        <header class="cp-modal-header">
            <div>
                <h3>Authenticator App Verification</h3>
                <p class="cp-modal-subtitle">
                    Open your Authenticator app and enter the 6-digit code.
                </p>
            </div>
            <button class="cp-modal-close" id="login-app2fa-close">&times;</button>
        </header>

        <div class="cp-modal-body">
            <div style="display:flex; justify-content:center; margin-top:0.5rem;">
                <input id="login-app2fa-code"
                       type="text"
                       inputmode="numeric"
                       maxlength="6"
                       autocomplete="one-time-code"
                       style="width:180px; height:55px; text-align:center;
                              font-size:1.6rem; letter-spacing:0.25em; border-radius:10px;
                              border:1px solid #ccc;">
            </div>

            <div id="login-app2fa-error"
                 style="color:#b00020; margin-top:1rem; text-align:center; display:none;">
            </div>

            <button id="login-app2fa-submit"
                    class="cp-btn cp-teal-btn"
                    style="margin-top:1.5rem; width:100%;">
                Verify Code
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
{{-- Existing EMAIL 2FA script (unchanged) --}}
<script>
(function(){

    const digits      = [...document.querySelectorAll('.login-2fa-digit')];
    const submitBtn   = document.getElementById('login-2fa-submit');
    const resendBtn   = document.getElementById('login-2fa-resend');
    const errorBox    = document.getElementById('login-2fa-error');
    const modalSheet  = document.querySelector('.cp-modal-sheet');
    const closeBtn    = document.getElementById('login-2fa-close');
    const backdrop    = document.getElementById('login-2fa-backdrop');

    /* ---------------------------- */
    /* 1. CLOSE MODAL LOGIC         */
    /* ---------------------------- */
    if (closeBtn && backdrop) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            backdrop.classList.remove('cp-modal-visible');
        });
    }

    if (!digits.length) return;

    /* ---------------------------- */
    /* 2. AUTO-ADVANCE + AUTOFILL   */
    /* ---------------------------- */
    digits.forEach((input, idx) => {

        input.addEventListener('input', (e) => {
            // Strip non-numeric characters
            let val = e.target.value.replace(/\D/g, '');

            // === MOBILE AUTOFILL & PASTE HANDLER ===
            // If value length > 1, it's a paste or keyboard suggestion
            if (val.length > 1) {
                const chars = val.split('');
                
                // Distribute characters to this box and subsequent boxes
                chars.forEach((char, i) => {
                    if (digits[idx + i]) {
                        digits[idx + i].value = char;
                    }
                });

                // Move focus to the last filled box (or the end)
                let focusIndex = idx + chars.length;
                if (focusIndex >= 5) focusIndex = 5; 
                digits[focusIndex].focus();
                
                checkReady();
                return; // Stop here so we don't trigger single-digit logic
            }
            
            // === SINGLE DIGIT TYPING ===
            // Even with maxlength=6, we enforce single char visually here
            e.target.value = val; 

            if (val && idx < 5) {
                digits[idx + 1].focus();
            }

            checkReady();
        });

        input.addEventListener('keydown', (e) => {
            // Handle Backspace
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                digits[idx - 1].focus();
            }
        });

        // Desktop Paste Fallback (Double protection)
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6)
                .split('');

            digits.forEach((box, i) => box.value = paste[i] || '');
            checkReady();
            if (digits[5].value) digits[5].focus();
        });
    });

    function checkReady() {
        const code = digits.map(i => i.value).join('');
        if (submitBtn) {
            submitBtn.disabled = code.length !== 6;
        }
    }

    function showError(msg) {
        if (!errorBox || !modalSheet) return;
        errorBox.innerText = msg || 'Something went wrong.';
        errorBox.style.display = 'block';

        modalSheet.classList.remove('shake');
        void modalSheet.offsetWidth;
        modalSheet.classList.add('shake');
    }

    /* ---------------------------- */
    /* 3. VERIFY 2FA CODE (EMAIL)   */
    /* ---------------------------- */
    if (submitBtn) {
        submitBtn.addEventListener('click', function(){
            const code = digits.map(i => i.value).join('');

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
                digits.forEach(i => i.value = '');
                digits[0].focus();
                submitBtn.disabled = true;
                showError(res.message);
            });
        });
    }

    if (resendBtn) {
        resendBtn.addEventListener('click', function(){
            fetch("{{ route('customer.security.email.send-login-code') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
            });
        });
    }

})();
</script>

{{-- New AUTHENTICATOR APP 2FA script --}}
<script>
(function(){

    const backdrop   = document.getElementById('login-app2fa-backdrop');
    if (!backdrop) return; // no app modal on this request

    const modalSheet = backdrop.querySelector('.cp-modal-sheet');
    const closeBtn   = document.getElementById('login-app2fa-close');
    const codeInput  = document.getElementById('login-app2fa-code');
    const submitBtn  = document.getElementById('login-app2fa-submit');
    const errorBox   = document.getElementById('login-app2fa-error');

    // Close logic
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e){
            e.preventDefault();
            backdrop.classList.remove('cp-modal-visible');
        });
    }

    // Helper: clean / normalise code
    function getCode() {
        if (!codeInput) return '';
        return (codeInput.value || '').replace(/\D/g, '').slice(0, 6);
    }

    function showError(msg) {
        if (!errorBox || !modalSheet) return;
        errorBox.innerText = msg || 'Something went wrong.';
        errorBox.style.display = 'block';

        modalSheet.classList.remove('shake');
        void modalSheet.offsetWidth;
        modalSheet.classList.add('shake');
    }

    if (codeInput) {
        codeInput.addEventListener('input', function(e){
            const cleaned = (e.target.value || '').replace(/\D/g, '').slice(0, 6);
            e.target.value = cleaned;
        });

        codeInput.addEventListener('keydown', function(e){
            if (e.key === 'Enter' && submitBtn) {
                e.preventDefault();
                submitBtn.click();
            }
        });

        // Focus as soon as modal shows
        setTimeout(() => codeInput.focus(), 100);
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function(){
            const code = getCode();

            if (code.length !== 6) {
                showError('Please enter the full 6-digit code.');
                return;
            }

            errorBox.style.display = 'none';

            fetch("{{ route('customer.login.2fa.verify') }}", {
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
                if (codeInput) {
                    codeInput.value = '';
                    codeInput.focus();
                }
                showError(res.message || 'Invalid or expired code.');
            })
            .catch(() => {
                showError('Something went wrong verifying your code.');
            });
        });
    }

})();
</script>
@endpush
