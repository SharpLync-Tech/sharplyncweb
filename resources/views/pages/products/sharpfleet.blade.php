@extends('layouts.base')

@section('title', 'About SharpFleet | A SharpLync SaaS Product')
@section('meta_description', 'Learn about SharpFleet, SharpLync’s connected fleet and job management SaaS platform for vehicles, field work, customers, scheduling, costs and reporting.')
@section('canonical', rtrim(config('seo.site_url'), '/') . route('products.sharpfleet', [], false))

@push('styles')
<link rel="stylesheet" href="{{ secure_asset('css/pages/sharpfleet-product.css') }}?v={{ @filemtime(public_path('css/pages/sharpfleet-product.css')) ?: time() }}">
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
                'url' => 'https://sharpfleet.com.au/',
                'mainEntityOfPage' => $sharpFleetUrl,
                'applicationCategory' => 'BusinessApplication',
                'applicationSubCategory' => 'Fleet and job management software',
                'operatingSystem' => 'Any device with a modern web browser',
                'description' => 'A browser-based SaaS platform from SharpLync that connects fleet management and job management while allowing either product to be used independently.',
                'publisher' => ['@id' => config('seo.business.id')],
                'featureList' => [
                    'Fleet trips, logbooks, vehicles, drivers and equipment',
                    'Fleet bookings, fuel, tolls, safety, compliance and reporting',
                    'Optional phone-based GPS without installed vehicle hardware',
                    'Customer requests, jobs, quotes and revisions',
                    'Technician and vehicle scheduling',
                    'Field time, photos, receipts, costing and margins',
                    'Billing review and accounting handoff',
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
                <p class="sf-product-lead">SharpFleet is a software-as-a-service product created and operated by SharpLync. It connects fleet and job management so organisations can keep vehicles, field work, customers, schedules, costs and reporting in context.</p>
            </div>
            <aside class="sf-product-saas-card">
                <span>SaaS</span>
                <h2>Software as a Service</h2>
                <p>SharpFleet works in a web browser on phones, tablets and computers. Fleet Management and Job Management can be used independently or together, with optional phone-based GPS available for fleet operations.</p>
            </aside>
        </div>
    </header>

    <nav class="sf-product-breadcrumbs sf-product-container" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a><span>/</span><span>Products</span><span>/</span><span>SharpFleet</span>
    </nav>

    <section class="sf-product-section sf-product-container">
        <div class="sf-product-section-heading">
            <p class="sf-product-kicker">What it is</p>
            <h2>Two areas of work in one connected platform</h2>
            <p>SharpFleet brings together the operational records surrounding fleets and field jobs. The two products are available independently, so an organisation can start with the area it needs, or use both to connect people, vehicles, customers and work without maintaining separate records.</p>
        </div>

        <div class="sf-product-pillar-grid">
            <section class="sf-product-pillar">
                <p class="sf-product-pillar-label">Vehicles, plant and drivers</p>
                <h3>Fleet Management</h3>
                <p>Tools for managing everyday fleet activity, assets, costs and compliance.</p>
                <ul>
                    <li>Trips and logbooks, including purpose, distance and engine hours</li>
                    <li>Vehicles, machinery, drivers, branches and shared assets</li>
                    <li>Bookings, fuel, tolls, tyres and operating costs</li>
                    <li>Servicing, registration, insurance and document reminders</li>
                    <li>Safety checks, fault reporting and compliance records</li>
                    <li>Reports, exports and optional phone-based GPS tracking</li>
                </ul>
            </section>

            <section class="sf-product-pillar">
                <p class="sf-product-pillar-label">Customers, field work and costs</p>
                <h3>Job Management</h3>
                <p>Tools that help office and field teams carry work from request through to billing review.</p>
                <ul>
                    <li>Customer requests, jobs, quotes, revisions and acceptance</li>
                    <li>Visual scheduling for technicians and vehicles</li>
                    <li>Customer sites, messages, files and portal access</li>
                    <li>Mobile field updates, time entries, photos and receipts</li>
                    <li>Labour, travel, materials, costs and margin visibility</li>
                    <li>Billing review, reports and accounting handoff</li>
                </ul>
            </section>
        </div>
    </section>

    <section class="sf-product-section sf-product-section-dark">
        <div class="sf-product-container sf-product-two-column">
            <div>
                <p class="sf-product-kicker">How it fits together</p>
                <h2>Keep the vehicle and the work in context</h2>
                <p>When both parts are used together, teams can schedule the people and vehicles required for a job, capture information in the field, and review fleet activity and job costs from one connected platform. Automation can assist with intake and administration while people remain in control of review and approval.</p>
            </div>
            <ul class="sf-product-checklist">
                <li>Use Fleet Management, Job Management or both</li>
                <li>Designed for office, field and road-based teams</li>
                <li>Accessible on phones, tablets and desktops</li>
                <li>Optional GPS uses phones rather than installed hardware</li>
                <li>Suitable for small and growing organisations</li>
            </ul>
        </div>
    </section>

    <section class="sf-product-section sf-product-container sf-product-two-column">
        <div>
            <p class="sf-product-kicker">Part of SharpLync</p>
            <h2>A SharpLync product with its own dedicated website</h2>
            <p>SharpFleet is a product and trademark of SharpLync Pty Ltd. It has its own name, website and application, while its design, development and supporting services remain part of SharpLync.</p>
            <p>This page explains the product’s relationship to SharpLync. The dedicated SharpFleet website contains the current feature details and access to the product.</p>
        </div>
        <div class="sf-product-callout">
            <h3>Visit SharpFleet</h3>
            <p>Open the official SharpFleet website for full product information.</p>
            <a href="https://sharpfleet.com.au" target="_blank" rel="noopener noreferrer">Open sharpfleet.com.au in a new tab ↗</a>
        </div>
    </section>
</article>
@endsection
