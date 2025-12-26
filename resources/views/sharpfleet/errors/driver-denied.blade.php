@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="container py-5 text-center">
    <h1 class="display-4 text-danger">Access Denied</h1>
    <p class="lead">You do not have permission to access this page.</p>
    <p>If you believe this is an error, please contact your administrator.</p>
    <a href="/app/sharpfleet" class="btn btn-primary mt-3">Return to Dashboard</a>
</div>
@endsection
