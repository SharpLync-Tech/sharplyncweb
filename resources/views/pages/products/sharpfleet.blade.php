@extends('layouts.base')

@section('title', 'SharpFleet SaaS Fleet Management Software | SharpLync')
@section('meta_description', 'SharpFleet is SharpLync’s browser-based SaaS fleet management product for trips, logbooks, vehicle bookings, fuel receipts, safety checks, reminders and reports.')
@section('canonical', rtrim(config('seo.site_url'), '/') . route('products.sharpfleet', [], false))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/sharpfleet-product.css') }}">
@endpush

@php
    $sharpFleetUrl = rtrim(config('seo.site_url'), '/') . route('products.sharpfleet', [], false);
    $sharpFleetSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'SoftwareApplication',
                '@id' => $sharpFleetUrl . '#software',
                'name' => 'SharpFleet',
                'url' => $sharpFleetUrl,
                'applicationCategory' => 'BusinessApplication',
                'applicationSubCategory' => 'Fleet management software',
                'operatingSystem' => 'Any device with a modern web browser',
                'description' => 'Browser-based SaaS fleet management for trip and logbook records, bookings, receipts, safety checks, reminders and reporting.',
                'publisher' => ['@id' => config('seo.business.id')],
                'featureList' => [
                    'Trip and logbook records for distance and engine hours',
                    'Driver, vehicle and equipment management',
                    'Vehicle and equipment bookings',
                    'Fuel receipt capture',
                    'Safety checks and issue reporting',
                    'Registration and service reminders',
                    'Operational and compliance reporting',
                    'No GPS tracking or installed vehicle hardware',
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(config('seo.site_url'), '/') . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'SharpFleet', 'item' => $sharpFleetUrl],
                ],
            ],
        ],
    ];
@endphp

@push('structured_data')
<script type="application/ld+json">@json($sharpFleetSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
@endpush

@section('content')
<article class="sf-product-page">
    <header class="sf-product-hero">
        <div class="sf-product-container sf-product-hero-grid">
            <div>
                <p class="sf-product-eyebrow">A SharpLync SaaS product</p>
                <img class="sf-product-logo" src="{{ asset('images/sharpfleet/logo.png') }}" alt="SharpFleet">
                <h1>Fleet management without GPS tracking, hardware or unnecessary complexity</h1>
                <p class="sf-product-lead">SharpFleet is browser-based fleet management software for businesses that need clear trip, vehicle, equipment and compliance records—without turning every vehicle into a surveillance device.</p>
                <div class="sf-product-actions">
                    <a class="sf-product-button sf-product-button-primary" href="/sharpfleet">Explore SharpFleet</a>
                    <a class="sf-product-button sf-product-button-secondary" href="/app/sharpfleet/admin/register">Start a free trial</a>
                </div>
            </div>
            <aside class="sf-product-saas-card">
                <span>SaaS</span>
                <h2>Software as a Service</h2>
                <p>Access SharpFleet through a web browser on phones, tablets and computers. SharpLync operates and improves the software while your team uses one shared system for day-to-day fleet records.</p>
            </aside>
        </div>
    </header>

    <nav class="sf-product-breadcrumbs sf-product-container" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a><span>/</span><span>Products</span><span>/</span><span>SharpFleet</span>
    </nav>

    <section class="sf-product-section sf-product-container">
        <div class="sf-product-section-heading">
            <p class="sf-product-kicker">One connected workspace</p>
            <h2>What SharpFleet manages</h2>
            <p>SharpFleet brings the records surrounding vehicles, plant, equipment and drivers into a practical system that can be used by both drivers and administrators.</p>
        </div>
        <div class="sf-product-feature-grid">
            <section><h3>Trips and logbooks</h3><p>Record business and private trips, purposes, distance and engine hours as work happens.</p></section>
            <section><h3>Vehicles and drivers</h3><p>Manage drivers, vehicles, shared assets, permissions and the operational information attached to them.</p></section>
            <section><h3>Bookings</h3><p>Reserve shared vehicles and equipment so teams can see availability and avoid scheduling conflicts.</p></section>
            <section><h3>Fuel and receipts</h3><p>Capture fuel information and receipt images against the relevant vehicle and records.</p></section>
            <section><h3>Safety and faults</h3><p>Use configurable pre-drive checks and record vehicle issues or accidents for administrator follow-up.</p></section>
            <section><h3>Reminders and reports</h3><p>Track registration and servicing reminders, then produce operational and compliance-focused reports.</p></section>
        </div>
    </section>

    <section class="sf-product-section sf-product-section-dark">
        <div class="sf-product-container sf-product-two-column">
            <div>
                <p class="sf-product-kicker">Designed around trust</p>
                <h2>No GPS tracking. No installed vehicle hardware.</h2>
                <p>SharpFleet focuses on useful records and clear workflows rather than continuous driver surveillance. It is designed for organisations that want accountability without introducing unnecessary hardware, monitoring or complexity.</p>
            </div>
            <ul class="sf-product-checklist">
                <li>Works with cars, utes, trucks, plant and equipment</li>
                <li>Supports distance and engine-hour records</li>
                <li>Suitable for shared and pool vehicles</li>
                <li>Accessible on phones, tablets and desktops</li>
                <li>Built for small and growing fleets</li>
            </ul>
        </div>
    </section>

    <section class="sf-product-section sf-product-container sf-product-two-column">
        <div>
            <p class="sf-product-kicker">Who it is for</p>
            <h2>Fleet tools for real-world operations</h2>
            <p>SharpFleet is suited to trades, service businesses, community organisations and growing teams that share vehicles or equipment and need better records without adopting a large enterprise fleet platform.</p>
        </div>
        <div class="sf-product-callout">
            <h3>Already using SharpFleet?</h3>
            <p>Open the secure application to access your driver or administrator workspace.</p>
            <a href="/app/sharpfleet/login">Sign in to SharpFleet →</a>
        </div>
    </section>

    <section class="sf-product-cta">
        <div class="sf-product-container">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="" aria-hidden="true">
            <h2>See how SharpFleet works</h2>
            <p>Visit the full SharpFleet product site for feature details, pricing, common questions and account registration.</p>
            <a class="sf-product-button sf-product-button-primary" href="/sharpfleet">Visit SharpFleet</a>
        </div>
    </section>
</article>
@endsection
