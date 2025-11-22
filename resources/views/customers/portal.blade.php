{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v2.7 (Email 2FA – Send + Verify Flow)
  Updated: 22 Nov 2025 by Max (ChatGPT)
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

    // Masked email: jo****@domain.com
    $maskedEmail = null;
    if ($email && str_contains($email, '@')) {
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 2);
        $stars   = max(1, mb_strlen($local) - 2);
        $maskedEmail = $visible . str_repeat('*', $stars) . '@' . $domain;
    }
@endphp

<div class="cp-pagehead">
    <h2>Customer Portal</h2>
</div>

<div class="cp-card cp-dashboard-grid">
    {{-- LEFT COLUMN: Profile --}}
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

    {{-- RIGHT COLUMN: Activity --}}
    <div class="cp-activity-column">

        {{-- SECURITY CARD --}}
        <div class="cp-activity-card cp-security-card">
            <h4>Security</h4>
            <p>Manage your login security and two-factor authentication options.</p>
            <div class="cp-security-footer">
                <button type="button"
                        id="cp-open-security-modal"
                        class="cp-btn cp-small-btn cp-teal-btn">
                    Manage Security
                </button>
            </div>
        </div>

        {{-- SUPPORT CARD --}}
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

        {{-- ACCOUNT SUMMARY CARD --}}
        <div class="cp-activity-card cp-account-card">
            <h4>Account Summary</h4>
            <p>Review your account status, services, and billing details.</p>
            <div class="cp-account-footer">
                <a href="{{ route('customer.account') }}" class="cp-btn cp-small-btn cp-teal-btn">View Account</a>
            </div>
        </div>

    </div>
</div>

{{-- ============================= --}}
{{-- SECURITY SLIDE-UP MODAL      --}}
{{-- ============================= --}}
<div id="cp-security-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet" role="dialog" aria-modal="true" aria-labelledby="cpSecurityTitle">

        {{-- HEADER (shared, text swapped by JS) --}}
        <header class="cp-modal-header">
            <div>
                <h3 id="cpSecurityTitle">Security &amp; Login Protection</h3>
                <p class="cp-modal-subtitle">
                    Manage how you protect access to your SharpLync customer portal.
                </p>
            </div>
            <button type="button" class="cp-modal-close" aria-label="Close security panel">
                &times;
            </button>
        </header>

        {{-- BODY: two "screens" inside the same modal --}}
        <div class="cp-modal-body">

            {{-- SCREEN 1: Main Security Options --}}
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

                        <label class="cp-switch">
                            <input id="cp-toggle-email"
                                   type="checkbox"
                                   data-setting="email">
                            <span class="cp-slider cp-slider-teal"></span>
                        </label>
                    </div>
                </div>

                {{-- AUTHENTICATOR --}}
                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M12 2a5 5 0 0 0-5 
                                             5v3H6c-1.1 0-2 .9-2 
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
                                    Use Google Authenticator or a compatible app to verify logins.
                                </p>
                            </div>
                        </div>

                        <label class="cp-switch">
                            <input id="cp-toggle-auth"
                                   type="checkbox"
                                   data-setting="authenticator">
                            <span class="cp-slider cp-slider-teal"></span>
                        </label>
                    </div>
                </div>

                {{-- SMS (DISABLED FOR NOW) --}}
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
                                <p class="cp-sec-desc">
                                    A mobile number is required before SMS authentication can be enabled.
                                </p>
                            </div>
                        </div>

                        <label class="cp-switch disabled">
                            <input id="cp-toggle-sms"
                                   type="checkbox"
                                   disabled
                                   data-setting="sms">
                            <span class="cp-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- SCREEN 2: Email 2FA Setup + Verification --}}
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
                                    Confirm it’s really you before we enable email-based two-factor authentication.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <p class="cp-sec-desc" style="margin-bottom: .3rem;">
                            We will send a verification code to your email address:
                        </p>
                        @if($maskedEmail)
                            <p style="font-weight: 600; margin: 0 0 .75rem;">
                                {{ $maskedEmail }}
                            </p>
                        @else
                            <p style="font-weight: 600; margin: 0 0 .75rem;">
                                (No email address on file)
                            </p>
                        @endif

                        <p class="cp-sec-desc">
                            Enter the code you receive to complete setup and turn on Email Authentication for your account.
                        </p>

                        {{-- Status / info line --}}
                        <p id="cp-email-status"
                           class="cp-sec-desc"
                           style="margin-top:.75rem; display:none;"></p>

                        {{-- STEP 1: Send Code Block --}}
                        <div id="cp-email-send-block" style="margin-top: 1rem;">
                            <button type="button"
                                    id="cp-email-setup-send"
                                    class="cp-btn cp-teal-btn">
                                Send Verification Code
                            </button>
                        </div>

                        {{-- STEP 2: Verify Code Block --}}
                        <div id="cp-email-verify-block" style="margin-top: 1.25rem; display:none;">

                            <p class="cp-sec-desc" style="margin-bottom: .5rem;">
                                Enter the 6-digit code we emailed you:
                            </p>

                            <div id="cp-email-otp-row"
                                 style="display:flex; gap:0.45rem; justify-content:flex-start; margin-bottom:0.75rem; flex-wrap:nowrap;">
                                @for($i = 0; $i < 6; $i++)
                                    <input type="text"
                                           maxlength="1"
                                           inputmode="numeric"
                                           pattern="[0-9]*"
                                           class="cp-otp-input"
                                           style="
                                               width: 2.35rem;
                                               height: 2.7rem;
                                               text-align: center;
                                               font-size: 1.4rem;
                                               border-radius: 8px;
                                               border: 1px solid #d0d7e2;
                                               outline: none;
                                               font-family: 'Poppins', sans-serif;
                                           ">
                                @endfor
                            </div>

                            <button type="button"
                                    id="cp-email-setup-verify"
                                    class="cp-btn cp-teal-btn">
                                Verify &amp; Enable
                            </button>

                            <button type="button"
                                    id="cp-email-setup-resend"
                                    class="cp-btn cp-small-btn cp-navy-btn"
                                    style="margin-left:.5rem;">
                                Resend Code
                            </button>

                            <p id="cp-email-error"
                               class="cp-modal-note"
                               style="display:none; margin-top:.75rem; color:#b3261e;">
                                Invalid or expired code. Please try again.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- FOOTER --}}
        <footer class="cp-modal-footer">
            <button type="button"
                    id="cp-email-setup-back"
                    class="cp-btn cp-small-btn cp-navy-btn"
                    style="display:none; margin-right: .5rem;">
                Back
            </button>

            <button type="button"
                    class="cp-btn cp-small-btn cp-navy-btn cp-modal-close-btn">
                Close
            </button>
        </footer>
    </div>
</div>
@endsection


@section('scripts')
<script>
    (function () {
        const openBtn      = document.getElementById('cp-open-security-modal');
        const modal        = document.getElementById('cp-security-modal');
        if (!openBtn || !modal) return;

        const sheet        = modal.querySelector('.cp-modal-sheet');
        const closeButtons = modal.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');
        const root         = document.querySelector('.cp-root');

        const modalTitle    = document.getElementById('cpSecurityTitle');
        const modalSubtitle = modal.querySelector('.cp-modal-subtitle');

        const screenMain    = document.getElementById('cp-modal-screen-main');
        const screenEmail   = document.getElementById('cp-modal-screen-email-setup');

        const emailToggle   = document.getElementById('cp-toggle-email');
        const authToggle    = document.getElementById('cp-toggle-auth');
        const smsToggle     = document.getElementById('cp-toggle-sms');

        const backBtn       = document.getElementById('cp-email-setup-back');
        const sendBtn       = document.getElementById('cp-email-setup-send');
        const verifyBtn     = document.getElementById('cp-email-setup-verify');
        const resendBtn     = document.getElementById('cp-email-setup-resend');

        const statusEl      = document.getElementById('cp-email-status');
        const errorEl       = document.getElementById('cp-email-error');
        const sendBlock     = document.getElementById('cp-email-send-block');
        const verifyBlock   = document.getElementById('cp-email-verify-block');
        const otpInputs     = Array.from(document.querySelectorAll('.cp-otp-input'));

        const defaultTitle    = modalTitle ? modalTitle.textContent : '';
        const defaultSubtitle = modalSubtitle ? modalSubtitle.textContent : '';

        const routes = {
            sendEmailCode: "{{ route('customer.security.email.send-code') }}",
            verifyEmailCode: "{{ route('customer.security.email.verify-code') }}"
        };
        const csrfToken = "{{ csrf_token() }}";

        function clearOtpInputs() {
            otpInputs.forEach(inp => inp.value = '');
            if (otpInputs[0]) otpInputs[0].focus();
        }

        function resetEmailSetupState() {
            if (statusEl) {
                statusEl.style.display = 'none';
                statusEl.textContent = '';
            }
            if (errorEl) {
                errorEl.style.display = 'none';
                errorEl.textContent = 'Invalid or expired code. Please try again.';
            }
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send Verification Code';
            }
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verify & Enable';
            }
            if (sendBlock)  sendBlock.style.display  = 'block';
            if (verifyBlock) verifyBlock.style.display = 'none';
            clearOtpInputs();
        }

        function showMainScreen() {
            if (screenMain)  screenMain.style.display  = 'block';
            if (screenEmail) screenEmail.style.display = 'none';

            if (modalTitle)    modalTitle.textContent    = defaultTitle;
            if (modalSubtitle) modalSubtitle.textContent = defaultSubtitle;

            if (backBtn) backBtn.style.display = 'none';

            // if user hasn't successfully enabled email 2FA, keep toggle off
            if (emailToggle && !emailToggle.dataset.persistOn) {
                emailToggle.checked = false;
            }

            resetEmailSetupState();
        }

        function showEmailSetupScreen() {
            if (screenMain)  screenMain.style.display  = 'none';
            if (screenEmail) screenEmail.style.display = 'block';

            if (modalTitle) {
                modalTitle.textContent = 'Set Up Email Authentication';
            }
            if (modalSubtitle) {
                modalSubtitle.textContent = 'We\'ll send a verification code to your email address.';
            }

            if (backBtn) backBtn.style.display = 'inline-block';

            resetEmailSetupState();
        }

        function openModal() {
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('cp-modal-visible');
            if (root) root.classList.add('modal-open');

            showMainScreen();
        }

        function closeModal() {
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'true');
            if (root) root.classList.remove('modal-open');

            showMainScreen();
        }

        openBtn.addEventListener('click', openModal);

        closeButtons.forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', function (e) {
            if (!sheet.contains(e.target)) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('cp-modal-visible')) {
                closeModal();
            }
        });

        // EMAIL toggle -> open setup screen
        if (emailToggle) {
            emailToggle.addEventListener('change', function () {
                if (this.checked) {
                    showEmailSetupScreen();
                } else {
                    showMainScreen();
                }
            });
        }

        // AUTH & SMS toggles: reserved for future
        if (authToggle) {
            authToggle.addEventListener('change', function () {
                console.log('Authenticator toggle changed:', this.checked);
            });
        }
        if (smsToggle) {
            smsToggle.addEventListener('change', function () {
                console.log('SMS toggle changed (disabled):', this.checked);
            });
        }

        // Back from Email Setup to main security options
        if (backBtn) {
            backBtn.addEventListener('click', function () {
                showMainScreen();
            });
        }

        // OTP input behaviour (auto-advance, backspace, paste full code)
        if (otpInputs.length) {
            otpInputs.forEach((input, idx) => {
                input.addEventListener('input', function (e) {
                    let val = e.target.value.replace(/\D/g, '');

                    if (!val) {
                        e.target.value = '';
                        return;
                    }

                    // If user typed/pasted more than one digit into a single box
                    if (val.length > 1) {
                        const digits = val.slice(0, otpInputs.length).split('');
                        otpInputs.forEach((inp, i) => {
                            inp.value = digits[i] || '';
                        });
                        const lastIndex = Math.min(digits.length - 1, otpInputs.length - 1);
                        otpInputs[lastIndex].focus();
                        return;
                    }

                    e.target.value = val;

                    // Auto advance
                    const next = otpInputs[idx + 1];
                    if (next && val) {
                        next.focus();
                        next.select && next.select();
                    }
                });

                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                        const prev = otpInputs[idx - 1];
                        prev.focus();
                        prev.select && prev.select();
                    }
                });

                input.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text') || '';
                    const digits = pasted.replace(/\D/g, '').slice(0, otpInputs.length).split('');
                    otpInputs.forEach((inp, i) => {
                        inp.value = digits[i] || '';
                    });
                    const focusIndex = Math.min(digits.length - 1, otpInputs.length - 1);
                    if (otpInputs[focusIndex]) {
                        otpInputs[focusIndex].focus();
                        otpInputs[focusIndex].select && otpInputs[focusIndex].select();
                    }
                });
            });
        }

        // Helper: collect 6-digit code from inputs
        function getOtpCode() {
            const digits = otpInputs.map(inp => inp.value.trim()).join('');
            return digits.replace(/\D/g, '');
        }

        // Helper: show status & error
        function showStatus(msg) {
            if (!statusEl) return;
            statusEl.textContent = msg;
            statusEl.style.display = 'block';
        }

        function showError(msg) {
            if (!errorEl) return;
            errorEl.textContent = msg || 'Invalid or expired code. Please try again.';
            errorEl.style.display = 'block';
        }

        function clearError() {
            if (!errorEl) return;
            errorEl.style.display = 'none';
        }

        // Send verification code (AJAX)
        async function sendEmailCode() {
            if (!sendBtn) return;

            clearError();
            showStatus('Sending verification code...');

            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';

            try {
                const res = await fetch(routes.sendEmailCode, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({})
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Unable to send verification code.');
                }

                showStatus('We\'ve emailed you a 6-digit code. Enter it below to finish setup.');
                if (sendBlock)  sendBlock.style.display  = 'none';
                if (verifyBlock) verifyBlock.style.display = 'block';
                clearOtpInputs();

            } catch (err) {
                console.error(err);
                showError(err.message || 'Unable to send verification code. Please try again.');
            } finally {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send Verification Code';
            }
        }

        // Verify code (AJAX)
        async function verifyEmailCode() {
            if (!verifyBtn) return;

            clearError();

            const code = getOtpCode();
            if (!code || code.length !== 6) {
                showError('Please enter the 6-digit code from your email.');
                return;
            }

            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Verifying...';

            try {
                const res = await fetch(routes.verifyEmailCode, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ code })
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Invalid or expired code.');
                }

                // Success: mark toggle as persist-on and checked
                if (emailToggle) {
                    emailToggle.dataset.persistOn = '1';
                    emailToggle.checked = true;
                }

                showStatus('Email Authentication is now enabled for your account.');
                // Optional: brief delay then go back to main screen
                setTimeout(() => {
                    showMainScreen();
                }, 800);

            } catch (err) {
                console.error(err);
                showError(err.message || 'Invalid or expired code. Please try again.');
            } finally {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verify & Enable';
            }
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', function () {
                sendEmailCode();
            });
        }

        if (resendBtn) {
            resendBtn.addEventListener('click', function () {
                sendEmailCode();
            });
        }

        if (verifyBtn) {
            verifyBtn.addEventListener('click', function () {
                verifyEmailCode();
            });
        }

    })();
</script>
@endsection