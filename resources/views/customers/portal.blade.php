{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v3.0 (Email 2FA + Authenticator App Setup)
  Updated: 24 Nov 2025 by Max (ChatGPT)
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Customer Portal')

@section('content')
@php
    use Illuminate\Support\Str;

    $u = isset($user) ? $user : (Auth::check() ? Auth::user() : null);
    $fullName = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'Customer Name';
    if ($fullName === '') $fullName = 'Customer Name';

    $email  = $u->email ?? null;
    $status = ucfirst($u->account_status ?? 'Active');
    $since  = $u && $u->created_at ? $u->created_at->format('F Y') : null;

    // Generate initials
    $nameParts = explode(' ', trim($fullName));
    $initials  = '';
    foreach ($nameParts as $p) {
        $initials .= strtoupper(Str::substr($p, 0, 1));
    }

    // Mask email
    $maskedEmail = null;
    if ($email && str_contains($email, '@')) {
        [$local, $domain] = explode('@', $email);
        $maskedEmail = mb_substr($local, 0, 2)
                        . str_repeat('*', max(1, mb_strlen($local) - 2))
                        . '@' . $domain;
    }
@endphp

<div class="cp-pagehead">
    <h2>Customer Portal</h2>
</div>

<div class="cp-card cp-dashboard-grid">
    {{-- LEFT COLUMN --}}
    <div class="cp-profile-card">
        <div class="cp-profile-header">
            <div class="cp-avatar">{{ $initials }}</div>
            <div class="cp-name-group">
                <h3>{{ $fullName }}</h3>
                <p class="cp-member-status">{{ $status }}</p>
                <p class="cp-detail-line">Email: <a href="mailto:{{ $email }}">{{ $email }}</a></p>
                @if($since)
                    <p class="cp-detail-line">Customer since: {{ $since }}</p>
                @endif
            </div>
        </div>

        <div class="cp-profile-actions">
            <a href="{{ route('customer.profile.edit') }}" class="cp-btn cp-edit-profile">Edit Profile</a>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="cp-activity-column">

        {{-- SECURITY CARD --}}
        <div class="cp-activity-card cp-security-card">
            <h4>Security</h4>
            <p>Manage your login security and two-factor authentication options.</p>
            <div class="cp-security-footer">
                <button id="cp-open-security-modal" class="cp-btn cp-small-btn cp-teal-btn">
                    Manage Security
                </button>
            </div>
        </div>

        {{-- SUPPORT --}}
        <div class="cp-activity-card cp-support-card">
            <h4>Support</h4>
            <p>Need help? View support tickets or connect for remote assistance.</p>
            <div class="cp-support-footer">
                <a href="{{ route('customer.support') }}" class="cp-btn cp-small-btn cp-teal-btn">Open Support</a>
                <a href="{{ URL::temporarySignedRoute('customer.teamviewer.download', now()->addMinutes(5)) }}"
                   class="cp-btn cp-small-btn cp-teal-btn">
                    Download Quick Support
                </a>
            </div>
        </div>

        {{-- ACCOUNT --}}
        <div class="cp-activity-card cp-account-card">
            <h4>Account Summary</h4>
            <p>Review your account status, services, and billing details.</p>
            <div class="cp-account-footer">
                <a href="{{ route('customer.account') }}" class="cp-btn cp-small-btn cp-teal-btn">View Account</a>
            </div>
        </div>

    </div>
</div>

{{-- ======================================================= --}}
{{-- SECURITY MODAL --}}
{{-- ======================================================= --}}
<div id="cp-security-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet">

        <header class="cp-modal-header">
            <div>
                <h3 id="cpSecurityTitle">Security & Login Protection</h3>
                <p class="cp-modal-subtitle">
                    Manage how you protect access to your SharpLync customer portal.
                </p>
            </div>
            <button class="cp-modal-close">&times;</button>
        </header>

        <div class="cp-modal-body">

            {{-- SCREEN 1 --}}
            <div id="cp-modal-screen-main">

                {{-- EMAIL 2FA --}}
                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 
                                             2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 
                                             2 0 0 0-2-2zm0 4-8 5-8-5V6l8 
                                             5 8-5v2z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Email Authentication</h4>
                                <p class="cp-sec-desc">
                                    Receive a one-time security code via email when signing in.
                                </p>
                            </div>
                        </div>

                        {{-- DB-bound toggle + persisted state --}}
                        <label class="cp-switch">
                            <input id="cp-toggle-email"
                                   type="checkbox"
                                   data-setting="email"
                                   @if($u->two_factor_email_enabled) checked @endif
                                   data-persist-on="{{ $u->two_factor_email_enabled ? '1' : '' }}">
                            <span class="cp-slider cp-slider-teal"></span>
                        </label>
                    </div>
                </div>

                {{-- AUTHENTICATOR APP --}}
                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M12 2a5 5 0 0 0-5 5v3H6c-1.1 0-2 .9-2 
                                             2v8c0 1.1.9 2 2 
                                             2h12c1.1 0 2-.9 
                                             2-2v-8c0-1.1-.9-2-2-2h-1V7a5 
                                             5 0 0 0-5-5zm-3 
                                             5a3 3 0 0 1 6 0v3H9V7z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Authenticator App</h4>
                                <p class="cp-sec-desc">
                                    Use a 6-digit code from Google Authenticator or another TOTP app.
                                </p>
                            </div>
                        </div>

                        <label class="cp-switch">
                            <input id="cp-toggle-auth"
                                   type="checkbox"
                                   data-setting="auth"
                                   @if($u->two_factor_app_enabled) checked @endif
                                   data-persist-on="{{ $u->two_factor_app_enabled ? '1' : '' }}">
                            <span class="cp-slider cp-slider-teal"></span>
                        </label>
                    </div>
                </div>

                {{-- SMS (disabled) --}}
                <div class="cp-sec-card cp-sec-bordered cp-sec-disabled">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M17 1H7C5.34 1 4 2.34 4 
                                             4v16c0 1.66 1.34 3 3 
                                             3h10c1.66 0 3-1.34 
                                             3-3V4c0-1.66-1.34-3-3-3zm0 
                                             18H7V5h10v14z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>SMS Verification</h4>
                            </div>
                        </div>

                        <label class="cp-switch disabled">
                            <input id="cp-toggle-sms" type="checkbox" disabled>
                            <span class="cp-slider"></span>
                        </label>
                    </div>
                </div>

            </div>

            {{-- ================================================= --}}
            {{-- SCREEN 2 — EMAIL SETUP --}}
            {{-- ================================================= --}}
            <div id="cp-modal-screen-email-setup" style="display:none;">

                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 
                                             2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 
                                             2 0 0 0-2-2zm0 4-8 5-8-5V6l8 
                                             5 8-5v2z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Set Up Email Authentication</h4>
                                <p class="cp-sec-desc">
                                    We’ll send a verification code to your email to confirm it’s you.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1rem;">
                        <p class="cp-sec-desc">Your email on file:</p>
                        <p style="font-weight:600; margin:0 0 .75rem;">{{ $maskedEmail }}</p>

                        <p id="cp-email-status" class="cp-sec-desc" style="display:none;"></p>

                        {{-- Send code --}}
                        <div id="cp-email-send-block" style="margin-top:1rem;">
                            <button id="cp-email-setup-send" class="cp-btn cp-teal-btn">
                                Send Verification Code
                            </button>
                        </div>

                        {{-- Verify code --}}
                        <div id="cp-email-verify-block" style="display:none; margin-top:1.25rem;">

                            <p class="cp-sec-desc">Enter the 6-digit code:</p>

                            <div id="cp-email-otp-row"
                                 style="display:flex; gap:.45rem; margin:.75rem 0;">
                                @for($i=0;$i<6;$i++)
                                    <input type="text" maxlength="1" inputmode="numeric"
                                           class="cp-otp-input"
                                           style="width:2.3rem; height:2.7rem;
                                                  text-align:center; font-size:1.4rem;">
                                @endfor
                            </div>

                            <button id="cp-email-setup-verify" class="cp-btn cp-teal-btn">
                                Verify & Enable
                            </button>

                            <button id="cp-email-setup-resend"
                                    class="cp-btn cp-small-btn cp-navy-btn"
                                    style="margin-left:.5rem;">
                                Resend Code
                            </button>

                            <p id="cp-email-error"
                               style="display:none; margin-top:.75rem; color:#b3261e;">
                                Invalid or expired code.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ================================================= --}}
            {{-- SCREEN 3 — AUTHENTICATOR APP SETUP --}}
            {{-- ================================================= --}}
            <div id="cp-modal-screen-auth-setup" style="display:none;">

                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M12 2a5 5 0 0 0-5 5v3H6c-1.1 0-2 .9-2 
                                             2v8c0 1.1.9 2 2 
                                             2h12c1.1 0 2-.9 
                                             2-2v-8c0-1.1-.9-2-2-2h-1V7a5 
                                             5 0 0 0-5-5zm-3 
                                             5a3 3 0 0 1 6 0v3H9V7z"/>
                                    </svg>
                            </span>
                            <div>
                                <h4>Set Up Authenticator App</h4>
                                <p class="cp-sec-desc">
                                    Scan the QR code with Google Authenticator or another TOTP app, 
                                    then enter the 6-digit code to confirm.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1rem;">
                        <div style="display:flex; flex-direction:column; align-items:center; gap:1rem;">
                            <div id="cp-auth-qr-wrapper" style="display:none;">
                                <img id="cp-auth-qr" src="" alt="Authenticator QR Code"
                                     style="width:180px; height:180px; border-radius:12px; background:#fff; padding:8px;">
                            </div>

                            <div id="cp-auth-secret-block" style="display:none; text-align:center;">
                                <p class="cp-sec-desc" style="margin-bottom:0.25rem;">Or enter this code manually in your app:</p>
                                <code id="cp-auth-secret" style="font-weight:700; letter-spacing:0.12em;"></code>
                            </div>

                            <button id="cp-auth-start" class="cp-btn cp-teal-btn">
                                Generate QR Code
                            </button>
                        </div>

                        <div id="cp-auth-verify-block" style="display:none; margin-top:1.5rem;">
                            <p class="cp-sec-desc">Enter the 6-digit code from your app:</p>
                            <input id="cp-auth-code"
                                   type="text"
                                   maxlength="6"
                                   inputmode="numeric"
                                   style="width:180px; height:2.7rem; text-align:center;
                                          font-size:1.4rem; letter-spacing:0.4em;">

                            <button id="cp-auth-verify" class="cp-btn cp-teal-btn" style="margin-top:1rem;">
                                Verify & Enable
                            </button>

                            <p id="cp-auth-status"
                               class="cp-sec-desc"
                               style="display:none; margin-top:.75rem;"></p>

                            <p id="cp-auth-error"
                               style="display:none; margin-top:.75rem; color:#b3261e;">
                            </p>
                        </div>

                        @if($u->two_factor_app_enabled)
                            <div style="margin-top:1.5rem; border-top:1px solid #e0e0e0; padding-top:1rem;">
                                <p class="cp-sec-desc">
                                    Authenticator app is currently <strong>enabled</strong> for your account.
                                </p>
                                <button id="cp-auth-disable" class="cp-btn cp-navy-btn">
                                    Disable Authenticator App
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <footer class="cp-modal-footer">
            <button id="cp-email-setup-back"
                    class="cp-btn cp-small-btn cp-navy-btn"
                    style="display:none; margin-right:.5rem;">
                Back
            </button>

            <button id="cp-auth-setup-back"
                    class="cp-btn cp-small-btn cp-navy-btn"
                    style="display:none; margin-right:.5rem;">
                Back
            </button>

            <button class="cp-btn cp-small-btn cp-navy-btn cp-modal-close-btn">
                Close
            </button>
        </footer>

    </div>
</div>
@endsection


@section('scripts')
<script>
(function(){

    const modal     = document.getElementById('cp-security-modal');
    const openBtn   = document.getElementById('cp-open-security-modal');
    const sheet     = modal.querySelector('.cp-modal-sheet');
    const closeBtns = modal.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');
    const root      = document.querySelector('.cp-root');

    const emailToggle = document.getElementById('cp-toggle-email');
    const authToggle  = document.getElementById('cp-toggle-auth');

    const screenMain  = document.getElementById('cp-modal-screen-main');
    const screenEmail = document.getElementById('cp-modal-screen-email-setup');
    const screenAuth  = document.getElementById('cp-modal-screen-auth-setup');

    const backEmailBtn = document.getElementById('cp-email-setup-back');
    const backAuthBtn  = document.getElementById('cp-auth-setup-back');

    const sendBtn    = document.getElementById('cp-email-setup-send');
    const resendBtn  = document.getElementById('cp-email-setup-resend');
    const verifyBtn  = document.getElementById('cp-email-setup-verify');

    const sendBlock   = document.getElementById('cp-email-send-block');
    const verifyBlock = document.getElementById('cp-email-verify-block');

    const statusEl   = document.getElementById('cp-email-status');
    const errorEl    = document.getElementById('cp-email-error');
    const otpInputs  = Array.from(document.querySelectorAll('.cp-otp-input'));

    // Authenticator elements
    const authStartBtn      = document.getElementById('cp-auth-start');
    const authVerifyBtn     = document.getElementById('cp-auth-verify');
    const authDisableBtn    = document.getElementById('cp-auth-disable');
    const authQrWrapper     = document.getElementById('cp-auth-qr-wrapper');
    const authQrImg         = document.getElementById('cp-auth-qr');
    const authSecretBlock   = document.getElementById('cp-auth-secret-block');
    const authSecretEl      = document.getElementById('cp-auth-secret');
    const authCodeInput     = document.getElementById('cp-auth-code');
    const authVerifyBlock   = document.getElementById('cp-auth-verify-block');
    const authStatusEl      = document.getElementById('cp-auth-status');
    const authErrorEl       = document.getElementById('cp-auth-error');

    const routes = {
        emailSend:   "{{ route('customer.security.email.send-code') }}",
        emailVerify: "{{ route('customer.security.email.verify-code') }}",
        authStart:   "{{ route('customer.security.auth.start') }}",
        authVerify:  "{{ route('customer.security.auth.verify') }}",
        authDisable: "{{ route('customer.security.auth.disable') }}"
    };
    const csrf = "{{ csrf_token() }}";

    function clearOtp() {
        otpInputs.forEach(i=>i.value='');
        if (otpInputs[0]) otpInputs[0].focus();
    }

    function restoreDBState() {
        if (emailToggle) {
            emailToggle.checked = (emailToggle.dataset.persistOn === "1");
        }
        if (authToggle) {
            authToggle.checked = (authToggle.dataset.persistOn === "1");
        }
    }

    function showMain() {
        screenMain.style.display  = 'block';
        screenEmail.style.display = 'none';
        screenAuth.style.display  = 'none';

        backEmailBtn.style.display = 'none';
        backAuthBtn.style.display  = 'none';

        errorEl.style.display   = 'none';
        statusEl.style.display  = 'none';
        sendBlock.style.display = 'block';
        verifyBlock.style.display = 'none';
        clearOtp();

        // Reset auth screen
        authQrWrapper.style.display   = 'none';
        authSecretBlock.style.display = 'none';
        authVerifyBlock.style.display = 'none';
        authStatusEl.style.display    = 'none';
        authErrorEl.style.display     = 'none';
        if (authCodeInput) authCodeInput.value = '';

        // restore DB value
        restoreDBState();
    }

    function showEmailSetup() {
        screenMain.style.display  = 'none';
        screenEmail.style.display = 'block';
        screenAuth.style.display  = 'none';

        backEmailBtn.style.display = 'inline-block';
        backAuthBtn.style.display  = 'none';

        errorEl.style.display  = 'none';
        statusEl.style.display = 'none';
        clearOtp();
    }

    function showAuthSetup() {
        screenMain.style.display  = 'none';
        screenEmail.style.display = 'none';
        screenAuth.style.display  = 'block';

        backEmailBtn.style.display = 'none';
        backAuthBtn.style.display  = 'inline-block';

        authStatusEl.style.display = 'none';
        authErrorEl.style.display  = 'none';
        if (authCodeInput) authCodeInput.value = '';
    }

    function openModal(){
        modal.classList.add('cp-modal-visible');
        modal.setAttribute('aria-hidden', 'false');
        if(root) root.classList.add('modal-open');
        showMain();
    }

    function closeModal(){
        modal.classList.remove('cp-modal-visible');
        modal.setAttribute('aria-hidden','true');
        if(root) root.classList.remove('modal-open');
        showMain();
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    closeBtns.forEach(btn=>btn.addEventListener('click', closeModal));

    modal.addEventListener('click', e=>{
        if (!sheet.contains(e.target)) closeModal();
    });

    // Email toggle
    if (emailToggle) {
        emailToggle.addEventListener('change', function(){
            if (this.checked) {
                // Move to email setup screen
                showEmailSetup();
                // Visual only: can't auto-disable app here; that’s handled in backend when we complete setup
            } else {
                // Just go back to main screen; actual disabling logic can be handled later if needed
                showMain();
            }
        });
    }

    backEmailBtn.addEventListener('click', showMain);

    // --- OTP INPUT BEHAVIOR ---
    otpInputs.forEach((input, idx)=>{
        input.addEventListener('input', e=>{
            e.target.value = e.target.value.replace(/\D/g,'');
            if (e.target.value && idx < otpInputs.length-1) {
                otpInputs[idx+1].focus();
            }
        });
        input.addEventListener('keydown', e=>{
            if(e.key==='Backspace' && !e.target.value && idx>0){
                otpInputs[idx-1].focus();
            }
        });
        input.addEventListener('paste', e=>{
            e.preventDefault();
            const digits = (e.clipboardData.getData('text')||'')
                .replace(/\D/g,'')
                .slice(0,6)
                .split('');
            otpInputs.forEach((inp,i)=>inp.value = digits[i]||'');
            otpInputs[Math.min(digits.length-1,5)].focus();
        });
    });

    function getOtp(){
        return otpInputs.map(i=>i.value).join('');
    }

    async function sendCode(){
        statusEl.style.display = 'block';
        statusEl.textContent = "Sending verification code...";
        try{
            const r = await fetch(routes.emailSend,{
                method:"POST",
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':csrf
                },
                body:JSON.stringify({})
            });
            const d = await r.json();
            if(!d.success) throw new Error(d.message);

            statusEl.textContent = "We've emailed you a 6-digit code.";
            sendBlock.style.display='none';
            verifyBlock.style.display='block';
            clearOtp();

        }catch(err){
            statusEl.style.display='none';
            errorEl.textContent = err.message || "Something went wrong.";
            errorEl.style.display='block';
        }
    }

    async function verifyCode(){
        const code = getOtp();
        if(code.length!==6){
            errorEl.textContent="Please enter the full 6-digit code.";
            errorEl.style.display='block';
            return;
        }

        try{
            const r = await fetch(routes.emailVerify,{
                method:"POST",
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':csrf
                },
                body:JSON.stringify({code})
            });
            const d = await r.json();
            if(!d.success) throw new Error(d.message);

            // Persist ON across sessions
            emailToggle.dataset.persistOn = "1";
            emailToggle.checked = true;

            statusEl.textContent = "Email Authentication is now enabled.";
            statusEl.style.display='block';
            errorEl.style.display='none';

            setTimeout(showMain,800);

        }catch(err){
            errorEl.textContent = err.message || "Invalid or expired code.";
            errorEl.style.display='block';
        }
    }

    if (sendBtn)   sendBtn.addEventListener('click', sendCode);
    if (resendBtn) resendBtn.addEventListener('click', sendCode);
    if (verifyBtn) verifyBtn.addEventListener('click', verifyCode);

    // ==========================================================
    // AUTHENTICATOR APP LOGIC
    // ==========================================================

    // When user flips the Authenticator toggle
    if (authToggle) {
        authToggle.addEventListener('change', function () {

            // Turned ON → start setup flow
            if (this.checked) {
                // Visual only: email toggle should appear off (real disabling is backend when GA is confirmed)
                if (emailToggle) {
                    emailToggle.checked = false;
                }

                showAuthSetup();

            } else {
                // Turned OFF
                // If DB says it's actually enabled, call disable endpoint
                if (authToggle.dataset.persistOn === "1") {
                    // Simple confirm for now
                    if (confirm("Disable Authenticator App for your account?")) {
                        disableAuth();
                    } else {
                            // Revert visual state
                            authToggle.checked = true;
                    }
                } else {
                    showMain();
                }
            }
        });
    }

    if (backAuthBtn) {
        backAuthBtn.addEventListener('click', showMain);
    }

        // Start / regenerate QR + secret
    async function startAuthSetup() {
        authStatusEl.style.display = 'block';
        authStatusEl.textContent  = "Generating QR code...";
        authErrorEl.style.display = 'none';

        try {
            const r = await fetch(routes.authStart, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({})
            });

            const d = await r.json();
            if (!d.success) throw new Error(d.message || "Unable to start setup.");

            // Show QR + secret
            const secret  = d.secret;
            const qrImage = d.qr_image || null;

            if (qrImage) {
                authQrImg.src = qrImage;
                authQrWrapper.style.display   = 'block';
            } else {
                // Fallback if QR generation failed: hide QR but still show secret
                authQrWrapper.style.display   = 'none';
            }

            if (secret) {
                authSecretBlock.style.display = 'block';
                authSecretEl.textContent      = secret;
            }

            authVerifyBlock.style.display = 'block';
            authStatusEl.textContent      = "Scan the QR or enter the code in your app, then enter the 6-digit code below.";

        } catch (err) {
            authStatusEl.style.display = 'none';
            authErrorEl.textContent    = err.message || "Something went wrong starting Authenticator setup.";
            authErrorEl.style.display  = 'block';
            // Revert toggle if setup fails
            if (authToggle) {
                authToggle.checked = false;
            }
        }
    }

    async function verifyAuthCode() {
        const code = (authCodeInput.value || '').replace(/\D/g, '');

        if (code.length !== 6) {
            authErrorEl.textContent   = "Please enter the full 6-digit code from your app.";
            authErrorEl.style.display = 'block';
            return;
        }

        authErrorEl.style.display  = 'none';
        authStatusEl.style.display = 'block';
        authStatusEl.textContent   = "Verifying code...";

        try {
            const r = await fetch(routes.authVerify, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({ code })
            });

            const d = await r.json();
            if (!d.success) throw new Error(d.message || "Invalid or expired code.");

            // Mark app 2FA as enabled in UI
            authToggle.dataset.persistOn = "1";
            authToggle.checked           = true;

            // Email is disabled when app is enabled
            if (emailToggle) {
                emailToggle.dataset.persistOn = "";
                emailToggle.checked           = false;
            }

            authStatusEl.textContent = "Authenticator App is now enabled for your account.";
            authErrorEl.style.display = 'none';

            // After a short pause, go back to main
            setTimeout(showMain, 900);

        } catch (err) {
            authStatusEl.style.display = 'none';
            authErrorEl.textContent    = err.message || "Invalid or expired code.";
            authErrorEl.style.display  = 'block';
        }
    }

    async function disableAuth() {
        authErrorEl.style.display  = 'none';
        authStatusEl.style.display = 'block';
        authStatusEl.textContent   = "Disabling Authenticator App...";

        try {
            const r = await fetch(routes.authDisable, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({})
            });

            const d = await r.json();
            if (!d.success) throw new Error(d.message || "Unable to disable Authenticator.");

            // Update UI
            authToggle.dataset.persistOn = "";
            authToggle.checked           = false;

            // Clear QR + code
            authQrWrapper.style.display   = 'none';
            authSecretBlock.style.display = 'none';
            authVerifyBlock.style.display = 'none';
            authStatusEl.textContent      = "Authenticator App has been disabled.";
            authStatusEl.style.display    = 'block';

        } catch (err) {
            authStatusEl.style.display = 'none';
            authErrorEl.textContent    = err.message || "Something went wrong disabling Authenticator.";
            authErrorEl.style.display  = 'block';
            // Keep toggle ON if disable fails
            authToggle.checked = true;
        }
    }

    if (authStartBtn)  authStartBtn.addEventListener('click', startAuthSetup);
    if (authVerifyBtn) authVerifyBtn.addEventListener('click', verifyAuthCode);
    if (authDisableBtn) authDisableBtn.addEventListener('click', disableAuth);

})();
</script>
@endsection
