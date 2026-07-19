@extends('layouts.base')

@section('title', 'Computer Repairs Stanthorpe | Laptop & Desktop Help | SharpLync')
@section('meta_description', 'Computer repairs in Stanthorpe for business laptops and desktops, including fault diagnosis, screens, batteries, upgrades, setup and software troubleshooting.')
@section('canonical', rtrim(config('seo.site_url'), '/') . route('computer-repairs.stanthorpe', [], false))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/local-seo.css') }}">
@endpush

@php
    $repairUrl = rtrim(config('seo.site_url'), '/') . route('computer-repairs.stanthorpe', [], false);
    $homeUrl = rtrim(config('seo.site_url'), '/') . '/';
    $servicesUrl = rtrim(config('seo.site_url'), '/') . route('services', [], false);
    $repairFaqs = [
        ['question' => 'What kinds of computer repairs do you handle?', 'answer' => 'SharpLync can assess laptop and desktop faults, slow performance, software problems, storage and memory upgrades, batteries, screens, setup and related business-computer issues. Parts availability and device condition determine the repair options.'],
        ['question' => 'Do you repair computers on site?', 'answer' => 'Some diagnosis, setup and component work can be completed on site by arrangement. Repairs requiring parts, extended testing or careful disassembly may need to be completed off site.'],
        ['question' => 'Can you help with a slow computer?', 'answer' => 'Yes. Slow performance can be caused by software, storage, insufficient memory, unwanted programs, updates or failing hardware. We diagnose the likely cause before recommending an upgrade or replacement.'],
        ['question' => 'Can you recover files from a failed computer?', 'answer' => 'We can assess common file-access and storage problems and explain the available options. Specialist recovery may be recommended where a drive has severe physical damage or the data is especially critical.'],
        ['question' => 'Should I repair or replace my computer?', 'answer' => 'That depends on the age, specifications, fault, parts cost and how the computer is used. SharpLync will explain the practical trade-off rather than recommending a repair that does not make commercial sense.'],
    ];

    $repairSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                '@id' => $repairUrl . '#service',
                'name' => 'Computer Repairs Stanthorpe',
                'serviceType' => 'Laptop and desktop computer repairs',
                'url' => $repairUrl,
                'description' => 'Computer fault diagnosis, repairs, upgrades and setup for businesses in Stanthorpe and the Granite Belt.',
                'provider' => ['@id' => config('seo.business.id')],
                'areaServed' => array_map(fn ($area) => ['@type' => 'Place', 'name' => $area], config('seo.business.areas_served')),
            ],
            [
                '@type' => 'FAQPage',
                '@id' => $repairUrl . '#faq',
                'mainEntity' => array_map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                ], $repairFaqs),
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $homeUrl],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => $servicesUrl],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => 'Computer Repairs Stanthorpe', 'item' => $repairUrl],
                ],
            ],
        ],
    ];
@endphp

@push('structured_data')
<script type="application/ld+json">@json($repairSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
@endpush

@section('content')
<article class="local-page">
    <header class="local-hero">
        <div class="local-container local-hero-grid">
            <div>
                <p class="local-eyebrow">Laptop and desktop help · Stanthorpe QLD 4380</p>
                <h1>Computer Repairs in Stanthorpe</h1>
                <p class="local-lead">Practical fault diagnosis, repair and upgrade advice for the computers local businesses rely on.</p>
                <div class="local-actions">
                    <a class="local-button local-button-primary" href="{{ url('/contact') }}">Ask about a repair</a>
                    <a class="local-button local-button-secondary" href="tel:0492014463">Call 0492 014 463</a>
                </div>
            </div>
            <aside class="local-summary" aria-label="Repair services summary">
                <h2>Repair and setup help</h2>
                <ul>
                    <li>Computer fault diagnosis</li>
                    <li>Laptop screens and batteries</li>
                    <li>Storage and memory upgrades</li>
                    <li>Slow or unstable computers</li>
                    <li>Software and Windows problems</li>
                    <li>New computer setup and transfer</li>
                </ul>
            </aside>
        </div>
    </header>

    <nav class="local-breadcrumbs local-container" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('services') }}">Services</a><span>/</span><span>Computer Repairs Stanthorpe</span>
    </nav>

    <section class="local-section local-container">
        <h2>Clear advice before money is spent</h2>
        <p>A computer that will not start, keeps freezing or takes too long to perform ordinary work can interrupt an entire day. SharpLync assesses the fault and explains whether repair, upgrade or replacement is the sensible option.</p>
        <p>We support business laptops and desktops in Stanthorpe and the Granite Belt. Repair options depend on the device, its condition and the availability of compatible parts, so contact us with the make, model and symptoms where possible.</p>
    </section>

    <section class="local-section local-section-tint">
        <div class="local-container">
            <h2>Common computer problems we assess</h2>
            <div class="local-card-grid">
                <section class="local-card"><h3>Slow performance</h3><p>Diagnosis of startup delays, low storage, memory limitations, unwanted software, update issues and ageing components.</p></section>
                <section class="local-card"><h3>Hardware faults</h3><p>Assessment of batteries, screens, storage, memory and other replaceable components, subject to parts availability.</p></section>
                <section class="local-card"><h3>Windows and software</h3><p>Help with errors, updates, application setup, account access and configuration problems affecting everyday work.</p></section>
                <section class="local-card"><h3>New computer setup</h3><p>Updates, security settings, business applications, Microsoft 365, printers and transfer of appropriate user data.</p></section>
                <section class="local-card"><h3>Security concerns</h3><p>Assessment after suspicious pop-ups, scams or malware concerns, followed by practical steps to improve protection.</p></section>
                <section class="local-card"><h3>Repair-or-replace advice</h3><p>An honest comparison of likely repair cost, device age, reliability and the requirements of the person using it.</p></section>
            </div>
        </div>
    </section>

    <section class="local-section local-container local-split">
        <div>
            <h2>More than a one-off repair</h2>
            <p>A recurring computer problem may point to a wider issue involving backups, Wi-Fi, Microsoft 365, security or equipment lifecycle planning. SharpLync can address the immediate fault and explain any related business risk without pressuring you into unnecessary services.</p>
        </div>
        <div class="local-callout">
            <h3>Need broader business support?</h3>
            <p>See our local support page for Microsoft 365, cybersecurity, networking, backups and ongoing assistance.</p>
            <a href="{{ route('it-support.stanthorpe') }}">IT support in Stanthorpe →</a>
        </div>
    </section>

    <section class="local-section local-section-tint">
        <div class="local-container local-faq">
            <h2>Computer repair FAQs</h2>
            @foreach($repairFaqs as $faq)
                <details>
                    <summary>{{ $faq['question'] }}</summary>
                    <p>{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <section class="local-cta">
        <div class="local-container">
            <h2>Tell us what your computer is doing</h2>
            <p>Include the device make and model, the symptoms, and when the problem began. We will advise the most useful next step.</p>
            <a class="local-button local-button-primary" href="{{ url('/contact') }}">Contact SharpLync</a>
        </div>
    </section>
</article>
@endsection
