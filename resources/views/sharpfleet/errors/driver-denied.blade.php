@extends('layouts.sharpfleet')

@section('title', 'Access Denied')

@section('sharpfleet-content')
<div class="auth-container">
    <div class="auth-card max-w-700">
        <div class="auth-header">
            <h1 class="auth-title">Access Denied</h1>
            <p class="auth-subtitle">You do not have permission to access this page.</p>
        </div>

        <div class="auth-content text-center">
            <p class="text-muted mb-3">If you believe this is an error, please contact your administrator.</p>
            <a href="/app/sharpfleet/login" class="btn-sf-navy">Return to Dashboard</a>
        </div>
    </div>
</div>
@endsection
