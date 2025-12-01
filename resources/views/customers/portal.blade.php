{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v3.1 (Security Card – Separate 2FA + Password Buttons)
  Updated: 30 Nov 2025 by Max (ChatGPT)
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
                
                {{-- NEW LABEL FOR OLD BUTTON --}}
                <button id="cp-open-security-modal"
                        class="cp-btn cp-small-btn cp-teal-btn">
                    2FA Settings
                </button>

                {{-- NEW PASSWORD BUTTON --}}
                <button id="cp-open-password-modal"
                        class="cp-btn cp-small-btn cp-navy-btn">
                    Password Settings
                </button>

            </div>
        </div>

        {{-- SUPPORT --}}
        <div class="cp-activity-card cp-support-card">
            <h4>Support</h4>
            <p>Need help? View support tickets or connect for remote assistance.</p>
            <div class="cp-support-footer">
                <a href="{{ route('customer.support.index') }}" class="cp-btn cp-small-btn cp-teal-btn">Open Support</a>
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
    @include('customers.security._modal')
</div>

@endsection

@section('scripts')
<script>
    window.cpRoutes = {
        emailSend:    "{{ route('customer.security.email.send-code') }}",
        emailVerify:  "{{ route('customer.security.email.verify-code') }}",
        emailDisable: "{{ route('customer.security.email.disable') }}",
        authStart:    "{{ route('customer.security.auth.start') }}",
        authVerify:   "{{ route('customer.security.auth.verify') }}",
        authDisable:  "{{ route('customer.security.auth.disable') }}"
    };
    window.cpCsrf = "{{ csrf_token() }}";
</script>

<script src="/js/portal-ui.js"></script>
<script src="/js/security.js"></script>
@endsection
