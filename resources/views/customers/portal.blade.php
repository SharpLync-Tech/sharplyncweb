{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v2.0.1 (Live Portal Base – $user-safe)
  Changes:
  - Switched from $customer -> $user (matches your users table schema).
  - Added safe fallbacks & null checks (no more "Undefined variable").
  - Preserves existing layout/classes/styles exactly.
  - Sections: Security / Support / Account Summary.
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Customer Portal')

@section('content')

@php
    // Prefer an explicitly-passed $user, fall back to Auth::user()
    $u = isset($user) ? $user : (Auth::check() ? Auth::user() : null);

    // Build display helpers safely
    $fullName = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'Customer Name';
    if ($fullName === '') $fullName = 'Customer Name';

    $email = $u->email ?? null;
    $phone = $u->phone ?? null;            // if you store it elsewhere, adjust later
    $address = $u->address ?? null;        // same here
    $status = $u->account_status ?? 'Active Customer';
    $since  = $u && $u->created_at ? $u->created_at->format('F Y') : null;
    $photo  = $u->profile_photo ?? null;

    // One-letter avatar placeholder
    $avatarLetter = strtoupper(mb_substr($fullName, 0, 1));
@endphp

<div class="cp-pagehead">
    <h2>Customer Portal</h2>
</div>

<div class="cp-card cp-dashboard-grid">
    
    {{-- LEFT COLUMN: Customer Profile --}}
    <div class="cp-profile-card">
        <div class="cp-profile-header">
            <div class="cp-avatar">
                @if(!empty($photo))
                    <img src="{{ Str::startsWith($photo, ['http://','https://','/']) ? $photo : asset('storage/'.$photo) }}" alt="{{ $fullName }}">
                @else
                    <div class="cp-avatar-placeholder">{{ $avatarLetter }}</div>
                @endif
            </div>

            <div class="cp-name-group">
                <h3>{{ $fullName }}</h3>
                <p class="cp-member-status">{{ ucfirst($status) }}</p>
            </div>
        </div>
        
        <div class="cp-contact-details">
            @if($email)
                <p><strong>Email:</strong> <a href="mailto:{{ $email }}">{{ $email }}</a></p>
            @endif

            @if($phone)
                <p><strong>Phone:</strong> {{ $phone }}</p>
            @endif

            @if($address)
                <p><strong>Address:</strong> {{ $address }}</p>
            @endif

            @if($since)
                <p class="cp-member-since">Customer since: {{ $since }}</p>
            @endif
        </div>
        
        <div class="cp-profile-actions">
            <a href="{{ route('customer.profile.edit') }}" class="cp-btn cp-edit-profile">Edit Profile</a>
            <form method="POST" action="{{ route('customer.logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="cp-btn cp-logout-btn">Log Out</button>
            </form>
        </div>
    </div>

    {{-- RIGHT COLUMN: Portal Cards --}}
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
