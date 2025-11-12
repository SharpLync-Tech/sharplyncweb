{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v2.2 (Initials Avatar + Detail + Mobile Polish)
  Updated: 13 Nov 2025 by Max (ChatGPT)
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
            <form method="POST" action="{{ route('customer.logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="cp-btn cp-logout-btn">Log Out</button>
            </form>
        </div>
    </div>

    {{-- RIGHT COLUMN: Cards --}}
    <div class="cp-activity-column">

        {{-- SECURITY CARD --}}
        <div class="cp-activity-card cp-security-card">
            <h4>Security</h4>
            <p>Manage your login security and two-factor authentication options.</p>
            <div class="cp-security-footer">
                <a href="{{ route('customer.security') }}" class="cp-btn cp-small-btn cp-teal-btn">Manage Security</a>
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
@endsection
