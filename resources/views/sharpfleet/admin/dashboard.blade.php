@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Admin Dashboard')

@section('sharpfleet-content')
<div class="mb-4">
    <h1 class="mb-2">SharpFleet Admin Dashboard</h1>
    <p>Welcome to SharpFleet. You are logged in as admin.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-3 mb-4">
    <div class="stats-card">
        <div class="stats-number">{{ $driversCount }}</div>
        <div class="stats-label">Drivers</div>
    </div>
    <div class="stats-card">
        <div class="stats-number">{{ $vehiclesCount }}</div>
        <div class="stats-label">Vehicles</div>
    </div>
    <div class="stats-card">
        <div class="stats-number">{{ $activeTripsCount ?? 0 }}</div>
        <div class="stats-label">Current Active Trips</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="d-flex gap-3 flex-wrap">
        <a href="/app/sharpfleet/admin/vehicles" class="btn btn-primary">Manage Vehicles</a>
        <a href="/app/sharpfleet/admin/reports/trips" class="btn btn-secondary">View Reports</a>
        <a href="/app/sharpfleet/admin/settings" class="btn btn-secondary">Settings</a>
        <a href="/app/sharpfleet/admin/company" class="btn btn-secondary">Company Overview</a>
    </div>
</div>

<div class="mt-4">
    <form method="POST" action="/app/sharpfleet/logout">
        @csrf
        <button type="submit" class="btn btn-danger">Log out of SharpFleet</button>
    </form>
</div>
@endsection
