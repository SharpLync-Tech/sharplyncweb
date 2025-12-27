@extends('layouts.sharpfleet')

@section('title', 'Company')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Company</h1>
        <p class="page-description">Overview of your organisation's configuration in SharpFleet.</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Company Details</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-2">
                <div class="info-item">
                    <div class="info-label">Company name</div>
                    <div class="info-value">{{ $companyName }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Type</div>
                    <div class="info-value">{{ $companyType }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Industry</div>
                    <div class="info-value">{{ $industry }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Timezone</div>
                    <div class="info-value">{{ $timezone }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-2 mb-4">
        <div class="stats-card">
            <h3 class="stats-label">Drivers</h3>
            <div class="stats-number">{{ $driversCount }}</div>
        </div>
        <div class="stats-card">
            <h3 class="stats-label">Vehicles</h3>
            <div class="stats-number">{{ $vehiclesCount }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Actions</h2>
        </div>
        <div class="card-body">
            <div class="btn-group">
                <a href="{{ url('/app/sharpfleet/admin/company/profile') }}" class="btn btn-primary">Edit Company Details</a>
                <a href="{{ url('/app/sharpfleet/admin/customers') }}" class="btn btn-secondary">Customers</a>
                <a href="{{ url('/app/sharpfleet/admin/users') }}" class="btn btn-secondary">Users / Drivers</a>
                <a href="{{ url('/app/sharpfleet/admin/settings') }}" class="btn btn-secondary">Company Settings</a>
                <a href="{{ url('/app/sharpfleet/admin/safety-checks') }}" class="btn btn-secondary">Safety Checks</a>
                <a href="{{ url('/app/sharpfleet/admin/reports/trips') }}" class="btn btn-secondary">Trip Reports</a>
                <a href="{{ url('/app/sharpfleet/admin/vehicles') }}" class="btn btn-secondary">Vehicles</a>
            </div>
        </div>
    </div>
</div>

@endsection
