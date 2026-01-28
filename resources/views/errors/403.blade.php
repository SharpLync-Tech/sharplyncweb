@extends('layouts.sharpfleet')

@section('title', 'Access denied')

@section('sharpfleet-content')
@php
    $sfUser = session('sharpfleet.user');

    $dashboardUrl = '/app/sharpfleet/login';
    if (is_array($sfUser)) {
        try {
            if (\App\Support\SharpFleet\Roles::isAdminPortal($sfUser)) {
                $dashboardUrl = '/app/sharpfleet/admin';
            } else {
                $dashboardUrl = '/app/sharpfleet/driver';
            }
        } catch (\Throwable $e) {
            $dashboardUrl = '/app/sharpfleet/login';
        }
    }
@endphp

<div class="auth-container">
    <div class="auth-card max-w-700">
        <div class="auth-header">
            <h1 class="auth-title">Access denied</h1>
            <p class="auth-subtitle">You don’t have access to this section.</p>
        </div>

        <div class="auth-content text-center">
            <p class="text-muted mb-3">If you believe this is a mistake, please contact your administrator.</p>

            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <button
                    type="button"
                    class="btn-sf-navy"
                    onclick="if (window.history && window.history.length > 1) { window.history.back(); } else { window.location.href='{{ $dashboardUrl }}'; }"
                >
                    Go back
                </button>
                <a href="{{ $dashboardUrl }}" class="btn-sf-navy">Go to dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
