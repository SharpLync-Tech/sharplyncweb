@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Terms & Conditions')

@section('sharpfleet-content')
<style>
    .sf-terms-card {
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 14px;
        background: #EEF3F8;
        box-shadow: 0 10px 18px rgba(10, 42, 77, 0.16);
    }
    .sf-terms-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #0A2A4D;
        font-weight: 600;
    }
    .sf-terms-divider,
    .policy-divider {
        height: 1px;
        background: rgba(10, 42, 77, 0.12);
        margin: 18px 0;
    }
    .policy-section + .policy-divider {
        margin-top: 8px;
    }
    .policy-section {
        margin-bottom: 14px;
    }
    .sf-terms-content h2 {
        font-size: 18px;
        margin-top: 18px;
        color: #0A2A4D;
    }
    .sf-terms-content h3 {
        font-size: 15px;
        margin-top: 14px;
        color: #0A2A4D;
    }
    .sf-terms-content p,
    .sf-terms-content li {
        color: #0A2A4D;
        font-size: 14px;
        line-height: 1.6;
    }
</style>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Terms & Conditions</h1>
        <p class="page-description">Please review the SharpFleet Terms & Conditions below.</p>
    </div>

    <div class="card sf-terms-card">
        <div class="card-body sf-terms-content">
            <div class="sf-terms-meta">
                <span>Version: v1.3</span>
                <span>Last updated: 1 January 2026</span>
            </div>

            <div class="sf-terms-divider"></div>

            @include('policies.sharpfleet-terms-content')
        </div>
    </div>
</div>
@endsection
