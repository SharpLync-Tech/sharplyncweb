@extends('layouts.sharpfleet')

@section('title', 'Reports')

@section('sharpfleet-content')

<style>
        .sf-report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
    }
    .sf-report-card {
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 14px;
        padding: 18px;
        background: #FFFFFF;
        box-shadow: 0 12px 20px rgba(10, 42, 77, 0.15);
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
    width: 36px;        /* ↑ from 28 */
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
        color: rgba(255, 255, 255, 0.65);
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

    <div class="sf-report-section-title">Operational Reports</div>
    <div class="sf-report-grid">
        <div class="sf-report-card">
            <h4>Fleet Manager – Operational</h4>
            <p>Daily and weekly overview of vehicle usage, idle vehicles, and last activity.</p>
            <div class="btn-group-inline">
                <a href="/app/sharpfleet/admin/reports/fleet-manager-operational" class="btn-sf-navy">View report</a>                
            </div>
        </div>

        <div class="sf-report-card">
            <h4>Vehicle Usage</h4>
            <p>Usage frequency, distance totals, and last active dates by vehicle.</p>
            <div class="btn-group-inline">
                <a href="/app/sharpfleet/admin/reports/vehicle-usage" class="btn-sf-navy">View report</a>
            </div>
        </div>
    </div>

    <div class="sf-report-section-title">Compliance &amp; Trip Reports</div>
    <div class="sf-report-grid">
        <div class="sf-report-card">
            <h4>Trips &amp; Compliance</h4>
            <p>Compliance-ready trip reporting with export for audit and review.</p>
            <div class="btn-group-inline">
                <a href="/app/sharpfleet/admin/reports/trips" class="btn-sf-navy">View report</a>                
            </div>
        </div>
    </div>

    <div class="sf-report-section-title">Future <span>(planned)</span></div>
    <div class="sf-report-grid">        

        <div class="sf-report-card">
            <h4>Faults by Vehicle</h4>
            <p>Breakdown of reported vehicle faults by asset and severity.</p>
            <div class="btn-group-inline">
                <button type="button" class="btn-sf-navy" disabled>View report</button>
            </div>
        </div>

        <div class="sf-report-card">
            <h4>Open Safety Issues</h4>
            <p>Active safety items that require attention or follow-up.</p>
            <div class="btn-group-inline">
                <button type="button" class="btn-sf-navy" disabled>View report</button>
            </div>
        </div>

        <div class="sf-report-card">
            <div class="sf-report-card__icon">
                <img src="/images/sharpfleet/ai.png" alt="AI">
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
                <a href="/app/sharpfleet/admin/reports/ai-report-builder" class="btn-sf-navy">View report</a>
            </div>
        </div>
    </div>
</div>

@endsection
