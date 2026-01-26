@extends('layouts.sharpfleet')

@section('title', 'Reports')

@section('sharpfleet-content')

@php
    $reportVisibility = $reportVisibility ?? [];
    $reportDefaults = [
        'trips_compliance' => true,
        'client_transport' => true,
        'fleet_manager_operational' => true,
        'vehicle_usage' => true,
        'utilization' => true,
        'faults_by_vehicle' => true,
        'safety_issues' => true,
        'vehicle_booking' => true,
        'ai_report_builder' => true,
    ];
    $reports = array_replace($reportDefaults, $reportVisibility);

    $showOperational = ($reports['fleet_manager_operational'] ?? false)
        || ($reports['vehicle_usage'] ?? false)
        || ($reports['utilization'] ?? false);
    $showCompliance = (bool) ($reports['trips_compliance'] ?? false)
        || (bool) ($reports['client_transport'] ?? false);
    $showFuture = ($reports['faults_by_vehicle'] ?? false)
        || ($reports['safety_issues'] ?? false)
        || ($reports['vehicle_booking'] ?? false)
        || ($reports['ai_report_builder'] ?? false);
@endphp

<style>
    .sf-report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
    }
    .sf-report-card {
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 14px;
        padding: 18px;
        background: rgba(240, 246, 252, 0.99);
        box-shadow:
            0 10px 18px rgba(10, 42, 77, 0.18),
            inset 0 1px 0 rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(6px);
        display: flex;
        flex-direction: column;
        min-height: 170px;
        position: relative;
    }

    .sf-report-card:hover {
        border: 1px solid rgba(44, 191, 174, 1.5); /* SharpFleet teal */
        border-radius: 14px;
        padding: 18px;
        box-shadow:
            0 0 0 1px rgba(44, 191, 174, 0.25),
            0 0 12px rgba(44, 191, 174, 0.35),
            0 12px 20px rgba(10, 42, 77, 0.15);
        display: flex;
        flex-direction: column;
        min-height: 170px;
    }
    .sf-report-card h4 {
        margin: 0 0 8px 0;
        font-size: 16px;
        color: navy;
        font-weight: 600;
    }
    .sf-report-card p {
        margin: 0 0 14px 0;
        color: navy;
        font-size: 13px;
        line-height: 1.5;
        flex: 1 1 auto;
    }
    .sf-report-card--ai {
        padding-right: 58px;
    }
    .sf-report-card__icon {
        position: absolute;
        top: 12px;          /* pull it in slightly */
        right: 12px;
        width: 36px;        /* up from 28 */
        height: 36px;
        opacity: 0.95;
        filter: drop-shadow(0 0 6px rgba(44, 191, 174, 0.35));
    }

    .sf-report-card__icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .sf-report-card__disclaimer {
        margin-top: -6px;
        margin-bottom: 12px;
        font-size: 11px;
        color: navy;
    }
    .sf-report-card .btn-group-inline {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .sf-report-section-title {
        margin: 18px 0 12px;
        font-size: 14px;
        font-weight: 700;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .sf-report-section-title span {
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
        color: rgba(255, 255, 255, 0.7);
        margin-left: 8px;
    }
</style>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Reports</h1>
        <p class="page-description">Operational and compliance reporting for SharpFleet.</p>
    </div>

    @if ($showOperational)
        <div class="sf-report-section-title">Operational Reports</div>
        <div class="sf-report-grid">
            @if ($reports['fleet_manager_operational'] ?? false)
                <div class="sf-report-card">
                    <h4>Fleet Manager - Operational</h4>
                    <p>Daily and weekly overview of vehicle usage, idle vehicles, and last activity.</p>
                    <div class="btn-group-inline">
                        <a href="/app/sharpfleet/admin/reports/fleet-manager-operational" class="btn-sf-navy">View report</a>
                    </div>
                </div>
            @endif

            @if ($reports['vehicle_usage'] ?? false)
                <div class="sf-report-card">
                    <h4>Vehicle Usage</h4>
                    <p>Usage frequency, distance totals, and last active dates by vehicle.</p>
                    <div class="btn-group-inline">
                        <a href="/app/sharpfleet/admin/reports/vehicle-usage" class="btn-sf-navy">View report</a>
                    </div>
                </div>
            @endif

            @if ($reports['utilization'] ?? false)
                <div class="sf-report-card">
                    <h4>Utilization Report</h4>
                    <p>Utilization percentages with visual bars to spot underused and overused vehicles.</p>
                    <div class="btn-group-inline">
                        <a href="/app/sharpfleet/admin/reports/utilization" class="btn-sf-navy">View report</a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($showCompliance)
        <div class="sf-report-section-title">Compliance &amp; Trip Reports</div>
        <div class="sf-report-grid">
            @if ($reports['trips_compliance'] ?? false)
                <div class="sf-report-card">
                    <h4>Trips &amp; Compliance</h4>
                    <p>Compliance-ready trip reporting with export for audit and review.</p>
                    <div class="btn-group-inline">
                        <a href="/app/sharpfleet/admin/reports/trips" class="btn-sf-navy">View report</a>
                    </div>
                </div>
            @endif

            @if ($reports['client_transport'] ?? false)
                <div class="sf-report-card">
                    <h4>Client Transport Report</h4>
                    <p>Client-focused trip view with timing, vehicle, driver, and distance details.</p>
                    <div class="btn-group-inline">
                        <a href="/app/sharpfleet/admin/reports/client-transport" class="btn-sf-navy">View report</a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($showFuture)
        <div class="sf-report-section-title">Future <span>(planned)</span></div>
        <div class="sf-report-grid">
            @if ($reports['faults_by_vehicle'] ?? false)
                <div class="sf-report-card">
                    <h4>Faults by Vehicle</h4>
                    <p>Breakdown of reported vehicle faults by asset and severity.</p>
                    <div class="btn-group-inline">
                        <button type="button" class="btn-sf-navy" disabled>View report</button>
                    </div>
                </div>
            @endif

            @if ($reports['safety_issues'] ?? false)
                <div class="sf-report-card">
                    <h4>Safety Issues</h4>
                    <p>Active safety items that require attention or follow-up.</p>
                    <div class="btn-group-inline">
                        <button type="button" class="btn-sf-navy" disabled>View report</button>
                    </div>
                </div>
            @endif

            @if ($reports['vehicle_booking'] ?? false)
                <div class="sf-report-card">
                    <h4>Vehicle Booking</h4>
                    <p>Summary view of vehicle bookings, upcoming reservations, and usage history.</p>
                    <div class="btn-group-inline">
                        <button type="button" class="btn-sf-navy" disabled>View report</button>
                    </div>
                </div>
            @endif

            @if ($reports['ai_report_builder'] ?? false)
                <div class="sf-report-card">
                    <div class="sf-report-card__icon">
                        
                    </div>
                    <h4>AI Report Builder (Beta)</h4>
                    <p>
                        Generate tailored fleet reports using AI-assisted analysis.
                        Designed for advanced users to explore data beyond standard reports.
                        All data processing occurs securely in Australia.
                    </p>
                    <div class="sf-report-card__disclaimer">
                        Beta features may change and are not recommended for formal reporting at this stage.
                    </div>
                    <div class="btn-group-inline">
                        <a href="/app/sharpfleet/admin/reports/ai-report-builder" class="btn-sf-navy">Build your Report</a>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

@endsection
