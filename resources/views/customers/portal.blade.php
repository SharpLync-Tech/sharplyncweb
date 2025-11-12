{{-- 
  Page: customers/portal.blade.php
  Version: v2.0 (Live Customer Portal Base)
  Description:
  - Cleaned up demo placeholders, keeping full grid layout and customer CSS style.
  - Renamed and restructured right-hand cards: Security / Support / Account Summary.
  - Left profile section updated to use dynamic data placeholders.
  - Added Logout button (same style as Edit Profile).
  - "Customer Details" → "Customer Portal".
  - "13/11/2025 - 6:41am"
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Customer Portal')

@section('content')

<div class="cp-pagehead">
    <h2>Customer Portal</h2>
</div>

<div class="cp-card cp-dashboard-grid">
    
    {{-- LEFT COLUMN: Customer Profile --}}
    <div class="cp-profile-card">
        <div class="cp-profile-header">
            <div class="cp-avatar">
                {{-- Optional: Display customer photo if available --}}
                @if(!empty($customer->photo))
                    <img src="{{ asset('storage/' . $customer->photo) }}" alt="{{ $customer->name }}">
                @else
                    <div class="cp-avatar-placeholder">{{ strtoupper(substr($customer->name, 0, 1)) }}</div>
                @endif
            </div>

            <div class="cp-name-group">
                <h3>{{ $customer->name ?? 'Customer Name' }}</h3>
                <p class="cp-member-status">{{ $customer->membership_level ?? 'Active Customer' }}</p>
            </div>
        </div>
        
        <div class="cp-contact-details">
            <p><strong>Email:</strong> <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></p>
            @if(!empty($customer->phone))
                <p><strong>Phone:</strong> {{ $customer->phone }}</p>
            @endif
            @if(!empty($customer->address))
                <p><strong>Address:</strong> {{ $customer->address }}</p>
            @endif
            @if(!empty($customer->created_at))
                <p class="cp-member-since">Customer Since: {{ $customer->created_at->format('F Y') }}</p>
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
            <p>Need help? Access your support tickets or connect for remote assistance.</p>
            <div class="cp-support-footer">
                <a href="{{ route('customer.support') }}" class="cp-btn cp-small-btn cp-navy-btn">Open Support</a>
                <a href="{{ route('customer.teamviewer.download') }}" class="cp-btn cp-small-btn cp-outline-btn">Download Quick Support</a>
            </div>
        </div>

        {{-- ACCOUNT SUMMARY CARD --}}
        <div class="cp-activity-card cp-account-card">
            <h4>Account Summary</h4>
            <p>Review your current account status, service plan, and payment details.</p>
            <div class="cp-account-footer">
                <a href="{{ route('customer.account') }}" class="cp-btn cp-small-btn cp-teal-btn">View Account</a>
            </div>
        </div>

    </div>
</div>

@endsection
