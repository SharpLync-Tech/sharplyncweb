@extends('layouts.sharpfleet')

@section('title', 'Access Denied')

@section('sharpfleet-content')

<div class="sf-access-wrapper">
    <div class="sf-access-card">

        {{-- Header --}}
        <div class="sf-access-header">
            <h1>Access Denied</h1>
            <p>You do not have permission to access this page.</p>
        </div>

        {{-- Body --}}
        <div class="sf-access-body">
            <p class="sf-access-note">
                If you believe this is an error, please contact your administrator.
            </p>

            <a href="/app/sharpfleet/login" class="btn btn-secondary">
                Return to Dashboard
            </a>
        </div>

    </div>
</div>

@endsection
