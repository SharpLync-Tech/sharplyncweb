{{-- 
  Page: resources/views/customers/security.blade.php
  Version: v1.1 (Matched Card Width Exactly)
  Description:
  - Keeps portal header + user header
  - Removes Edit Profile / Support / Account Summary
  - Card width now identical to Customer Portal right column
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Security Settings')

@section('content')
@php
    use Illuminate\Support\Str;

    $u = isset($user) ? $user : (Auth::check() ? Auth::user() : null);

    $fullName = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'Customer Name';
    if ($fullName === '') $fullName = 'Customer Name';

    $email = $u->email ?? null;
    $status = ucfirst($u->account_status ?? 'Active');
    $since = $u && $u->created_at ? $u->created_at->format('F Y') : null;

    // Generate initials
    $nameParts = explode(' ', trim($fullName));
    $initials = '';
    foreach ($nameParts as $p) {
        $initials .= strtoupper(Str::substr($p, 0, 1));
    }
@endphp

<div class="cp-pagehead">
    <h2>Security Settings</h2>
</div>

{{-- IDENTICAL STRUCTURE TO MAIN PORTAL --}}
<div class="cp-card cp-dashboard-grid">

    {{-- LEFT: Profile Column (unchanged) --}}
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
        {{-- IMPORTANT: No Edit Profile button --}}
    </div>

    {{-- RIGHT: Activity Column (forced to full width with CSS fix) --}}
    <div class="cp-activity-column">

        <div class="cp-activity-card cp-security-card">
            <h4>Two-Factor Authentication</h4>
            <p>Protect your account with an additional layer of security.</p>

            <div style="padding: .75rem 0; color: #6b7a89;">
                <em>2FA options will appear here.</em><br>
                Email 2FA, Google Authenticator, and SMS 2FA coming next.
            </div>
        </div>

    </div>

</div>
@endsection
