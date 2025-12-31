@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Admin Dashboard')

@section('sharpfleet-content')
<div class="hero">
    <h1>SharpFleet,<br><span class="highlight">Admin Dashboard</span></h1>
    <p>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@php
    $rem = $vehicleReminders ?? null;
    $hasReminders = $rem && (
        ($rem['rego_overdue'] ?? 0) > 0 || ($rem['rego_due_soon'] ?? 0) > 0 ||
        ($rem['service_date_overdue'] ?? 0) > 0 || ($rem['service_date_due_soon'] ?? 0) > 0 ||
        ($rem['service_reading_overdue'] ?? 0) > 0 || ($rem['service_reading_due_soon'] ?? 0) > 0
    );
    $hasOverdue = $rem && (
        ($rem['rego_overdue'] ?? 0) > 0 ||
        ($rem['service_date_overdue'] ?? 0) > 0 ||
        ($rem['service_reading_overdue'] ?? 0) > 0
    );
@endphp

<div class="grid grid-4 mb-4">
    <div class="stats-card">
        <div class="stats-number">{{ $driversCount }}</div>
        <div class="stats-label">Drivers</div>
    </div>
    <div class="stats-card">
        <div class="stats-number">{{ $vehiclesCount }}</div>
        <div class="stats-label">Vehicles</div>
    </div>
    @if(($hasOutOfServiceSupport ?? false))
        <a href="/app/sharpfleet/admin/vehicles" class="stats-card" style="text-decoration:none;">
            <div class="stats-number">{{ (int) ($outOfServiceVehiclesCount ?? 0) }}</div>
            <div class="stats-label">Vehicles Out of Service</div>
        </a>
    @endif
    <div class="stats-card">
        <div class="stats-number">{{ $activeTripsCount ?? 0 }}</div>
        <div class="stats-label">Current Active Trips</div>
    </div>
</div>

@if($hasReminders)
    <div class="stats-card text-left mb-4">
        <div class="d-flex justify-between align-items-center flex-wrap gap-2 mb-2">
            <div class="fw-bold">Vehicle reminders</div>
            <a href="/app/sharpfleet/admin/vehicles" class="btn btn-primary btn-sm">Review vehicles</a>
        </div>

        <div class="alert {{ $hasOverdue ? 'alert-danger' : 'alert-warning' }} mb-0" style="align-items:flex-start;">
            <div>
                <div class="fw-bold mb-1">{{ $hasOverdue ? 'Overdue items need attention' : 'Upcoming items due soon' }}</div>
                <ul class="mb-0" style="margin:0; padding-left: 18px;">
                    @if(($rem['rego_enabled'] ?? false) && ((int) ($rem['rego_overdue'] ?? 0) || (int) ($rem['rego_due_soon'] ?? 0)))
                        <li>
                            <strong>Rego:</strong>
                            {{ (int) ($rem['rego_overdue'] ?? 0) }} overdue,
                            {{ (int) ($rem['rego_due_soon'] ?? 0) }} due soon
                        </li>
                    @endif

                    @if(($rem['service_enabled'] ?? false) && ((int) ($rem['service_date_overdue'] ?? 0) || (int) ($rem['service_date_due_soon'] ?? 0)))
                        <li>
                            <strong>Service (date):</strong>
                            {{ (int) ($rem['service_date_overdue'] ?? 0) }} overdue,
                            {{ (int) ($rem['service_date_due_soon'] ?? 0) }} due soon
                        </li>
                    @endif

                    @if(($rem['service_enabled'] ?? false) && ((int) ($rem['service_reading_overdue'] ?? 0) || (int) ($rem['service_reading_due_soon'] ?? 0)))
                        <li>
                            <strong>Service (reading):</strong>
                            {{ (int) ($rem['service_reading_overdue'] ?? 0) }} overdue,
                            {{ (int) ($rem['service_reading_due_soon'] ?? 0) }} due soon
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endif
@endsection
