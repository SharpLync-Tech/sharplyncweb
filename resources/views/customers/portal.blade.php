{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v2.3 (Security Modal - Slide Up)
  Updated: 21 Nov 2025 by Max (ChatGPT)
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Customer Portal')

@section('content')
@php
    use Illuminate\Support\Str;
    $u = isset($user) ? $user : (Auth::check() ? Auth::user() : null);
    $fullName = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'Customer Name';
    if ($fullName === '') $fullName = 'Customer Name';
    $email = $u->email ?? null;
    $status = ucfirst($u->account_status ?? 'Active');
    $since = $u && $u->created_at ? $u->created_at->format('F Y') : null;

    // Generate initials (e.g., "JB")
    $nameParts = explode(' ', trim($fullName));
    $initials = '';
    foreach ($nameParts as $p) {
        $initials .= strtoupper(Str::substr($p, 0, 1));
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

    {{-- RIGHT COLUMN: Cards --}}
    <div class="cp-activity-column">

        {{-- SECURITY CARD --}}
        <div class="cp-activity-card cp-security-card">
            <h4>Security</h4>
            <p>Manage your login security and two-factor authentication options.</p>
            <div class="cp-security-footer">
                {{-- NOTE: Now opens modal instead of navigating --}}
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
                <a href="{{ route('customer.support') }}" class="cp-btn cp-small-btn cp-navy-btn">Open Support</a>
                <a href="{{ URL::temporarySignedRoute('customer.teamviewer.download', now()->addMinutes(5)) }}"
                   class="cp-btn cp-small-btn cp-outline-btn">
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

    {{-- ============================= --}}
    {{-- Email 2FA Card --}}
    {{-- ============================= --}}
    <div class="cp-sec-card">
        <div class="cp-sec-card-header">
            <div>
                <h4>Email Two-Factor Authentication</h4>
                <p class="cp-sec-desc">Receive a one-time code via email when signing in.</p>
            </div>

            <span class="cp-sec-status cp-status-off">Off</span>
        </div>

        <div class="cp-sec-card-footer">
            <button class="cp-btn cp-small-btn cp-teal-btn">Set Up</button>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- Google Authenticator Card --}}
    {{-- ============================= --}}
    <div class="cp-sec-card">
        <div class="cp-sec-card-header">
            <div>
                <h4>Authenticator App</h4>
                <p class="cp-sec-desc">Use Google Authenticator or compatible apps to verify sign-ins.</p>
            </div>

            <span class="cp-sec-status cp-status-off">Off</span>
        </div>

        <div class="cp-sec-card-footer">
            <button class="cp-btn cp-small-btn cp-teal-btn">Set Up</button>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- SMS 2FA Card --}}
    {{-- ============================= --}}
    <div class="cp-sec-card">
        <div class="cp-sec-card-header">
            <div>
                <h4>SMS Verification</h4>
                <p class="cp-sec-desc">Receive a 6-digit verification code via text message.</p>
            </div>

            <span class="cp-sec-status cp-status-off">Off</span>
        </div>

        <div class="cp-sec-card-footer">
            <button class="cp-btn cp-small-btn cp-teal-btn">Set Up</button>
        </div>
    </div>

</div> <!-- END cp-modal-body -->


        <footer class="cp-modal-footer">
            <button type="button" class="cp-btn cp-small-btn cp-navy-btn cp-modal-close-btn">
                Close
            </button>
        </footer>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const openBtn = document.getElementById('cp-open-security-modal');
        const modal   = document.getElementById('cp-security-modal');
        if (!openBtn || !modal) return;

        const sheet   = modal.querySelector('.cp-modal-sheet');
        const closeButtons = modal.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');

        function openModal() {
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('cp-modal-visible');
            document.body.style.overflow = 'hidden'; // lock scroll behind
        }

        function closeModal() {
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = ''; // restore scroll
        }

        openBtn.addEventListener('click', function () {
            openModal();
        });

        closeButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                closeModal();
            });
        });

        // Click outside sheet to close
        modal.addEventListener('click', function (e) {
            if (!sheet.contains(e.target)) {
                closeModal();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('cp-modal-visible')) {
                closeModal();
            }
        });
    })();
</script>
@endsection
