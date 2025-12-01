{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v4.1 (Stable: 2FA Modals Inline + Password/SSPIN Modal Added)
  Updated: 30 Nov 2025 by Max
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
            <div class="cp-avatar">
            @php
                $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : null;
                $initials = strtoupper(substr($user->first_name,0,1) . substr($user->last_name,0,1));
            @endphp
                
            @if($photo)
                <img id="current-avatar" src="{{ $photo }}" alt="Avatar">
            @else
                <span id="current-avatar-initials">{{ $initials }}</span>
            @endif
            </div>

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

            <div class="cp-security-footer" style="display:flex; gap:.5rem; flex-wrap:wrap;">
                
                {{-- 2FA Settings Button --}}
                <button id="cp-open-security-modal" class="cp-btn cp-small-btn cp-teal-btn">
                    2FA Settings
                </button>

                {{-- NEW Password + SSPIN Modal Button --}}
                <button id="cp-open-password-modal" class="cp-btn cp-small-btn cp-teal-btn">
                    Password & SSPIN Settings
                </button>

            </div>
        </div>

        {{-- SUPPORT --}}
        <div class="cp-activity-card cp-support-card">
            <h4>Support</h4>
            <p>Need help? View support tickets or connect for remote assistance.</p>
            <div class="cp-support-footer">
                <a href="{{ route('customer.support.index') }}" class="cp-btn cp-small-btn cp-teal-btn">
                    Open Support
                </a>

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
{{-- EXISTING FULL 2FA SECURITY MODAL (INLINE + WORKING) --}}
{{-- EXACT COPY of your current working version --}}
{{-- ======================================================= --}}

<div id="cp-security-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet">

        <header class="cp-modal-header">
            <div>
                <h3 id="cpSecurityTitle">Security & Login Protection</h3>
                <p class="cp-modal-subtitle">Manage how you protect access to your SharpLync portal.</p>
            </div>
            <button class="cp-modal-close">&times;</button>
        </header>

        <div class="cp-modal-body">
            <div class="cp-activity-card cp-security-card">
            <h4>Security</h4>
            <p>Manage your login security and two-factor authentication options.</p>
            <div class="cp-security-footer">
                <button id="cp-open-security-modal" class="cp-btn cp-small-btn cp-teal-btn">
                    Manage Security
                </button>
            </div>
        </div>
        </div>

        <footer class="cp-modal-footer">
            <button class="cp-btn cp-small-btn cp-navy-btn cp-modal-close-btn">
                Close
            </button>
        </footer>
    </div>
</div>



{{-- ======================================================= --}}
{{-- NEW PASSWORD & SSPIN MODAL --}}
{{-- ======================================================= --}}

<div id="cp-password-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet">

        <header class="cp-modal-header">
            <div>
                <h3>Password & SSPIN Settings</h3>
                <p class="cp-modal-subtitle">Update your login password or Support PIN.</p>
            </div>
            <button class="cp-password-close">&times;</button>
        </header>

        <div class="cp-modal-body">

            {{-- PASSWORD SECTION --}}
            <div class="cp-sec-card cp-sec-bordered">
                <h4>Change Password</h4>

                <label class="cp-sec-label">Current Password</label>
                <input type="password" id="cp-pass-current" class="cp-input">

                <label class="cp-sec-label">New Password</label>
                <input type="password" id="cp-pass-new" class="cp-input">

                <label class="cp-sec-label">Confirm Password</label>
                <input type="password" id="cp-pass-confirm" class="cp-input">

                <button id="cp-pass-save" class="cp-btn cp-teal-btn" style="margin-top:1rem;">
                    Update Password
                </button>

                <button id="cp-pass-forgot" class="cp-btn cp-small-btn cp-navy-btn" style="margin-top:.5rem;">
                    I Forgot My Password
                </button>

                <p id="cp-pass-msg" class="cp-sec-desc" style="display:none; margin-top:.5rem;"></p>
            </div>

            {{-- SSPIN SECTION --}}
            <div class="cp-sec-card cp-sec-bordered" style="margin-top:1.5rem;">
                <h4>Support PIN (SSPIN)</h4>

                <p class="cp-sec-desc">Your current Support PIN:</p>
                <p id="cp-sspin-display"
                   style="font-size:1.6rem; font-weight:700; letter-spacing:.25rem; margin-bottom:.5rem;">
                   ••••••
                </p>

                <button id="cp-sspin-toggle" class="cp-btn cp-small-btn cp-navy-btn">
                    Show PIN
                </button>

                <button id="cp-sspin-generate" class="cp-btn cp-small-btn cp-teal-btn" style="margin-left:.5rem;">
                    Generate New PIN
                </button>

                <div style="margin-top:1rem;">
                    <label class="cp-sec-label">Or enter your own PIN</label>
                    <input type="text" id="cp-sspin-input" maxlength="6" class="cp-input"
                           placeholder="123456" inputmode="numeric">
                </div>

                <button id="cp-sspin-save" class="cp-btn cp-teal-btn" style="margin-top:1rem;">
                    Save SSPIN
                </button>

                <p id="cp-sspin-msg" class="cp-sec-desc" style="display:none; margin-top:.5rem;"></p>
            </div>

        </div>

        <footer class="cp-modal-footer">
            <button class="cp-btn cp-small-btn cp-navy-btn cp-password-close">
                Close
            </button>
        </footer>

    </div>
</div>

@endsection


@section('scripts')
<script src="/js/portal-ui.js"></script>
<script src="/js/security.js"></script>
@endsection
