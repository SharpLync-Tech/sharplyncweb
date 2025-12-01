{{-- 
  Page: resources/views/customers/portal.blade.php
  Version: v3.0 (Modularised)
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Customer Portal')

@push('styles')
<link rel="stylesheet" href="/css/password-sspin.css">
@endpush

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
        @include('customers.portal.profile-card', ['u' => $u])

        {{-- RIGHT COLUMN --}}
        <div class="cp-activity-column">

            {{-- SECURITY CARD --}}
            @include('customers.portal.security-card', ['u' => $u])

            {{-- SUPPORT CARD --}}
            @include('customers.portal.support-card', ['u' => $u])

            {{-- ACCOUNT CARD --}}
            @include('customers.portal.account-card', ['u' => $u])

        </div>

</div>

{{-- ======================================================= --}}
{{-- MODALS (BOTH ORIGINAL, UNCHANGED)                       --}}
{{-- ======================================================= --}}

@include('customers.portal.modals.security-modal')
@include('customers.portal.modals.password-sspin-modal')

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
