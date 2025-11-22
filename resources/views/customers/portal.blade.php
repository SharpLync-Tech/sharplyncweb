{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v2.6 (Security Modal – Email 2FA Setup Screen)
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

            {{-- SCREEN 2: Email 2FA Setup --}}
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

                        <button type="button"
                                id="cp-email-setup-send"
                                class="cp-btn cp-teal-btn"
                                style="margin-top: 1rem;">
                            Send Verification Code
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- FOOTER (Back toggles on in setup screen) --}}
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

        const modalTitle   = document.getElementById('cpSecurityTitle');
        const modalSubtitle= modal.querySelector('.cp-modal-subtitle');

        const screenMain   = document.getElementById('cp-modal-screen-main');
        const screenEmail  = document.getElementById('cp-modal-screen-email-setup');

        const emailToggle  = document.getElementById('cp-toggle-email');
        const authToggle   = document.getElementById('cp-toggle-auth');
        const smsToggle    = document.getElementById('cp-toggle-sms');

        const backBtn      = document.getElementById('cp-email-setup-back');
        const sendBtn      = document.getElementById('cp-email-setup-send');

        const defaultTitle    = modalTitle ? modalTitle.textContent : '';
        const defaultSubtitle = modalSubtitle ? modalSubtitle.textContent : '';

        function showMainScreen() {
            if (screenMain)  screenMain.style.display  = 'block';
            if (screenEmail) screenEmail.style.display = 'none';

            if (modalTitle)   modalTitle.textContent   = defaultTitle;
            if (modalSubtitle)modalSubtitle.textContent= defaultSubtitle;

            if (backBtn) backBtn.style.display = 'none';

            // For now, if user leaves setup without completing, keep toggle OFF
            if (emailToggle && !emailToggle.dataset.persistOn) {
                emailToggle.checked = false;
            }
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
        }

        function openModal() {
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('cp-modal-visible');
            if (root) root.classList.add('modal-open');

            // When opening, always show main screen
            showMainScreen();
        }

        function closeModal() {
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'true');
            if (root) root.classList.remove('modal-open');

            // Reset back to main on close
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
                    // If they turn it off on main screen, just leave them on main for now
                    showMainScreen();
                }
            });
        }

        // AUTH & SMS toggles: placeholder for future wiring
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

        // Send Verification Code (placeholder – Step 3 will wire backend)
        if (sendBtn) {
            sendBtn.addEventListener('click', function () {
                console.log('TODO: Send verification code via backend');
                // In Step 3:
                //  - call POST /portal/security/2fa/email/send-code
                //  - show input for code
                //  - verify + enable
            });
        }

    })();
</script>
@endsection
