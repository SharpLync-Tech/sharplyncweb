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

@if($hasReminders)
    <div class="alert {{ $hasOverdue ? 'alert-danger' : 'alert-warning' }}">
        <strong>Vehicle reminders:</strong>
        @php
            $parts = [];
            if (($rem['rego_enabled'] ?? false) && (($rem['rego_overdue'] ?? 0) || ($rem['rego_due_soon'] ?? 0))) {
                $parts[] = "Rego overdue: " . (int) ($rem['rego_overdue'] ?? 0) . ", due soon: " . (int) ($rem['rego_due_soon'] ?? 0);
            }
            if (($rem['service_enabled'] ?? false) && (($rem['service_date_overdue'] ?? 0) || ($rem['service_date_due_soon'] ?? 0))) {
                $parts[] = "Service (date) overdue: " . (int) ($rem['service_date_overdue'] ?? 0) . ", due soon: " . (int) ($rem['service_date_due_soon'] ?? 0);
            }
            if (($rem['service_enabled'] ?? false) && (($rem['service_reading_overdue'] ?? 0) || ($rem['service_reading_due_soon'] ?? 0))) {
                $parts[] = "Service (reading) overdue: " . (int) ($rem['service_reading_overdue'] ?? 0) . ", due soon: " . (int) ($rem['service_reading_due_soon'] ?? 0);
            }
        @endphp
        {{ implode(' | ', $parts) }}
        <a href="/app/sharpfleet/admin/vehicles" class="ms-2">Review vehicles</a>
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
@endsection
