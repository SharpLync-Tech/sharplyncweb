@extends('layouts.base')

@section('title', 'IT Support Stanthorpe & Granite Belt | SharpLync')
@section('meta_description', 'Local IT support for Stanthorpe and Granite Belt businesses. Get help with Microsoft 365, cybersecurity, computers, Wi-Fi, backups and on-site or remote support.')
@section('canonical', rtrim(config('seo.site_url'), '/') . route('it-support.stanthorpe', [], false))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/local-seo.css') }}">
@endpush

@php
    $itSupportUrl = rtrim(config('seo.site_url'), '/') . route('it-support.stanthorpe', [], false);
    $homeUrl = rtrim(config('seo.site_url'), '/') . '/';
    $servicesUrl = rtrim(config('seo.site_url'), '/') . route('services', [], false);
    $itSupportFaqs = [
        ['question' => 'Do you provide on-site IT support in Stanthorpe?', 'answer' => 'Yes. SharpLync provides on-site support in Stanthorpe and across the Granite Belt by arrangement. We first confirm the issue and whether an on-site visit or secure remote session is the most effective option.'],
        ['question' => 'Can you support a small business without an IT department?', 'answer' => 'Yes. We help small businesses with day-to-day support, Microsoft 365, devices, networks, backups and practical technology planning without requiring an internal IT team.'],
        ['question' => 'What can be fixed remotely?', 'answer' => 'Many email, Microsoft 365, account, software, printer and computer configuration problems can be diagnosed remotely. Cabling, failed hardware and some network faults require an on-site visit.'],
        ['question' => 'Do you offer one-off and ongoing IT support?', 'answer' => 'Yes. SharpLync can assist with a specific issue or discuss ongoing support, monitoring and maintenance based on the needs of your business.'],
        ['question' => 'Which areas do you service?', 'answer' => 'Our local service area includes Stanthorpe, the Granite Belt and the wider Southern Downs. Remote support can also be provided to eligible businesses outside the local area.'],
    ];

    $itSupportSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                '@id' => $itSupportUrl . '#service',
                'name' => 'IT Support Stanthorpe',
                'serviceType' => 'Business IT support',
                'url' => $itSupportUrl,
                'description' => 'Local and remote IT support for businesses in Stanthorpe and the Granite Belt.',
                'provider' => ['@id' => config('seo.business.id')],
                'areaServed' => array_map(fn ($area) => ['@type' => 'Place', 'name' => $area], config('seo.business.areas_served')),
                'hasOfferCatalog' => [
                    '@type' => 'OfferCatalog',
                    'name' => 'IT support services',
                    'itemListElement' => array_map(fn ($name) => [
                        '@type' => 'Offer',
                        'itemOffered' => ['@type' => 'Service', 'name' => $name],
                    ], ['Remote IT support', 'On-site IT support', 'Microsoft 365 support', 'Cybersecurity support', 'Network and Wi-Fi support', 'Backup and recovery planning']),
                ],
            ],
            [
                '@type' => 'FAQPage',
                '@id' => $itSupportUrl . '#faq',
                'mainEntity' => array_map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                ], $itSupportFaqs),
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $homeUrl],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => $servicesUrl],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => 'IT Support Stanthorpe', 'item' => $itSupportUrl],
                ],
            ],
        ],
    ];
@endphp

@push('structured_data')
<script type="application/ld+json">@json($itSupportSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
@endpush

@section('content')
<article class="local-page">
    <header class="local-hero">
        <div class="local-container local-hero-grid">
            <div>
                <p class="local-eyebrow">Local technology help · Stanthorpe QLD 4380</p>
                <h1>Local IT Support for Stanthorpe and the Granite Belt</h1>
                <p class="local-lead">Straightforward help for local businesses—on-site when the job needs hands-on attention and secure remote support when it does not.</p>
                <div class="local-actions">
                    <a class="local-button local-button-primary" href="{{ url('/contact') }}">Talk to SharpLync</a>
                    <a class="local-button local-button-secondary" href="tel:0492014463">Call 0492 014 463</a>
                </div>
            </div>
            <aside class="local-summary" aria-label="Service summary">
                <h2>How we can help</h2>
                <ul>
                    <li>Remote and on-site troubleshooting</li>
                    <li>Microsoft 365 and cloud support</li>
                    <li>Cybersecurity and device protection</li>
                    <li>Business networks and Wi-Fi</li>
                    <li>Backup and continuity planning</li>
                    <li>Laptop and desktop assistance</li>
                </ul>
            </aside>
        </div>
    </header>

    <nav class="local-breadcrumbs local-container" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('services') }}">Services</a><span>/</span><span>IT Support Stanthorpe</span>
    </nav>

    <section class="local-section local-container">
        <h2>IT support built around regional businesses</h2>
        <p>Technology problems cost time whether you run a professional office, retail or hospitality venue, trade business, community organisation, or a small team working across several locations. SharpLync provides practical support without a distant call-centre experience.</p>
        <p>We start by understanding what has stopped working and what the business needs to achieve. Where appropriate, a secure remote session can resolve the problem quickly. Hardware, cabling, Wi-Fi coverage and other physical faults can be handled on site in Stanthorpe and the surrounding region by arrangement.</p>
    </section>

    <section class="local-section local-section-tint">
        <div class="local-container">
            <h2>Business IT services available in Stanthorpe</h2>
            <div class="local-card-grid">
                <section class="local-card"><h3>Everyday IT support</h3><p>Help with slow computers, software errors, printers, email, account access and the day-to-day issues that interrupt work.</p></section>
                <section class="local-card"><h3>Microsoft 365</h3><p>Setup and support for business email, Teams, OneDrive, SharePoint, licensing, user access and secure sign-in.</p></section>
                <section class="local-card"><h3>Cybersecurity</h3><p>Practical device protection, multi-factor authentication, patching, security reviews and assistance responding to suspicious activity.</p></section>
                <section class="local-card"><h3>Networks and Wi-Fi</h3><p>Fault finding, coverage improvement, business-grade networking, firewall and router support for reliable connectivity.</p></section>
                <section class="local-card"><h3>Backup and recovery</h3><p>Backup planning for computers, servers and cloud data, with attention to whether information can actually be recovered.</p></section>
                <section class="local-card"><h3>IT planning</h3><p>Clear advice on hardware replacement, cloud services, risk, budgeting and sensible next steps without unnecessary jargon.</p></section>
            </div>
        </div>
    </section>

    <section class="local-section local-container local-split">
        <div>
            <h2>Local when it matters, remote when it works</h2>
            <p>Not every issue requires travel and not every issue should be handled remotely. SharpLync chooses the approach based on the fault, security requirements and the fastest sensible path to a reliable result.</p>
            <p>Our stated local coverage includes Stanthorpe, the Granite Belt and the wider Southern Downs. Contact us to confirm availability for your location and the type of work required.</p>
        </div>
        <div class="local-callout">
            <h3>Need help with a computer?</h3>
            <p>For screens, batteries, upgrades and desktop or laptop faults, see our dedicated local repair information.</p>
            <a href="{{ route('computer-repairs.stanthorpe') }}">Computer repairs in Stanthorpe →</a>
        </div>
    </section>

    <section class="local-section local-section-tint">
        <div class="local-container local-faq">
            <h2>IT support FAQs</h2>
            @foreach($itSupportFaqs as $faq)
                <details>
                    <summary>{{ $faq['question'] }}</summary>
                    <p>{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <section class="local-cta">
        <div class="local-container">
            <h2>Talk to a local about your IT</h2>
            <p>Tell us what is happening, where you are located and whether the issue is urgent. We will explain the practical next step.</p>
            <a class="local-button local-button-primary" href="{{ url('/contact') }}">Contact SharpLync</a>
        </div>
    </section>
</article>
@endsection
