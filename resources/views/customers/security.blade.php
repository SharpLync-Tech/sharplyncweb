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

{{-- IDENTICAL STRUCTURE TO ORIGINAL DASHBOARD --}}
<div class="cp-card cp-dashboard-grid">

    {{-- LEFT PROFILE COLUMN (unchanged) --}}
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
    </div>

    {{-- RIGHT ACTIVITY COLUMN (kept EXACT width) --}}
    <div class="cp-activity-column">

        {{-- FULL-SIZE SECURITY CARD --}}
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
