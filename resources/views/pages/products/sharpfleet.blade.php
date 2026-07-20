@extends('layouts.base')

@section('title', 'About SharpFleet | A SharpLync SaaS Product')
@section('meta_description', 'Learn about SharpFleet, a fleet management SaaS product created and operated by SharpLync for practical vehicle, equipment and trip records.')
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
                <p class="sf-product-eyebrow">A product of SharpLync</p>
                <img class="sf-product-logo" src="{{ asset('images/sharpfleet/logo.png') }}" alt="SharpFleet">
                <h1>About SharpFleet</h1>
                <p class="sf-product-lead">SharpFleet is a software-as-a-service product created and operated by SharpLync. It gives organisations a practical way to manage fleet records without requiring GPS tracking or hardware installed in vehicles.</p>
            </div>
            <aside class="sf-product-saas-card">
                <span>SaaS</span>
                <h2>Software as a Service</h2>
                <p>SharpFleet is accessed through a web browser on phones, tablets and computers. SharpLync is responsible for the product, its ongoing development and its supporting services.</p>
            </aside>
        </div>
    </header>

    <nav class="sf-product-breadcrumbs sf-product-container" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a><span>/</span><span>Products</span><span>/</span><span>SharpFleet</span>
    </nav>

    <section class="sf-product-section sf-product-container">
        <div class="sf-product-section-heading">
            <p class="sf-product-kicker">What it is</p>
            <h2>A focused fleet record system</h2>
            <p>SharpFleet brings the everyday records surrounding vehicles, plant, equipment and drivers into one browser-based system. Drivers can record activity as it happens, while authorised administrators can manage the wider fleet and review its records.</p>
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
                <p class="sf-product-kicker">Why it exists</p>
                <h2>Built around useful records, not surveillance</h2>
                <p>SharpLync developed SharpFleet for organisations that need better fleet records but do not want the cost, complexity or workplace concerns that can come with continuous GPS monitoring. The product focuses on clear workflows and information people can actually use.</p>
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
            <p class="sf-product-kicker">Part of SharpLync</p>
            <h2>One of the products we build and support</h2>
            <p>SharpFleet has its own name, interface and product website, but it remains a SharpLync product. The same practical approach behind SharpLync’s technology services shapes how SharpFleet is designed, developed and supported.</p>
            <p>This page explains where SharpFleet fits within SharpLync. Detailed features, current pricing, account registration and product support are available on the dedicated SharpFleet website.</p>
        </div>
        <div class="sf-product-callout">
            <h3>Visit SharpFleet</h3>
            <p>Open the dedicated SharpFleet website to learn more about the product.</p>
            <a href="{{ url('/sharpfleet') }}" target="_blank" rel="noopener noreferrer">Open SharpFleet in a new tab ↗</a>
        </div>
    </section>
</article>
@endsection
