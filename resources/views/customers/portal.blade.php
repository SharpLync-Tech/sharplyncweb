{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v2.5 (Prepared for 2FA Backend: Email + Authenticator + SMS)
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

    $email    = $u->email ?? null;
    $status   = ucfirst($u->account_status ?? 'Active');
    $since    = $u && $u->created_at ? $u->created_at->format('F Y') : null;

    // Generate initials
    $nameParts = explode(' ', trim($fullName));
    $initials  = '';
    foreach ($nameParts as $p) {
        $initials .= strtoupper(Str::substr($p, 0, 1));
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
                <button type="button"
                        id="cp-open-security-modal"
                        class="cp-btn cp-small-btn cp-teal-btn">
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

{{-- ============================= --}}
{{-- SECURITY MODAL --}}
{{-- ============================= --}}

<div id="cp-security-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet" role="dialog" aria-modal="true" aria-labelledby="cpSecurityTitle">

        <header class="cp-modal-header">
            <div>
                <h3 id="cpSecurityTitle">Security &amp; Login Protection</h3>
                <p class="cp-modal-subtitle">Manage how you protect access to your SharpLync customer portal.</p>
            </div>

            <button type="button" class="cp-modal-close" aria-label="Close security panel">
                &times;
            </button>
        </header>

        <div class="cp-modal-body">

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
                            <p class="cp-sec-desc">Receive a one-time security code via email when signing in.</p>
                        </div>
                    </div>

                    <label class="cp-switch">
                        <input id="cp-toggle-email"
                               type="checkbox"
                               data-setting="email"
                               {{-- checked="{{ old('x',$u->twofa_email_enabled ?? false) ? 'checked' : '' }}" --}}
                        >
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
                            <p class="cp-sec-desc">Use Google Authenticator or a compatible app to verify logins.</p>
                        </div>
                    </div>

                    <label class="cp-switch">
                        <input id="cp-toggle-auth"
                               type="checkbox"
                               data-setting="authenticator"
                               {{-- checked="{{ old('x',$u->twofa_auth_enabled ?? false) ? 'checked' : '' }}" --}}
                        >
                        <span class="cp-slider cp-slider-teal"></span>
                    </label>
                </div>
            </div>

            {{-- SMS (DISABLED) --}}
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
                            <p class="cp-sec-desc">A mobile number is required before SMS authentication can be enabled.</p>
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

        <footer class="cp-modal-footer">
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

        /* -------------------------------
           Modal Core
        ------------------------------- */
        const openBtn      = document.getElementById('cp-open-security-modal');
        const modal        = document.getElementById('cp-security-modal');
        if (!openBtn || !modal) return;

        const sheet        = modal.querySelector('.cp-modal-sheet');
        const closeButtons = modal.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');
        const root         = document.querySelector('.cp-root');

        function openModal() {
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('cp-modal-visible');
            root?.classList.add('modal-open');
        }

        function closeModal() {
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'true');
            root?.classList.remove('modal-open');
        }

        openBtn.addEventListener('click', openModal);

        closeButtons.forEach(btn => btn.addEventListener('click', closeModal));

        // Close when clicking backdrop
        modal.addEventListener('click', e => {
            if (!sheet.contains(e.target)) closeModal();
        });

        // ESC closes modal
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && modal.classList.contains('cp-modal-visible'))
                closeModal();
        });

        /* -------------------------------
           Gather toggle switches (prep for Step 2)
        ------------------------------- */
        const emailToggle = document.getElementById('cp-toggle-email');
        const authToggle  = document.getElementById('cp-toggle-auth');
        const smsToggle   = document.getElementById('cp-toggle-sms');

        // In Step 2: we add fetch() POST logic here.
        // For now: console log test.
        [emailToggle, authToggle].forEach(input => {
            if (!input) return;
            input.addEventListener('change', () => {
                console.log("Toggle changed:", input.dataset.setting, input.checked);
            });
        });

    })();
</script>
@endsection
