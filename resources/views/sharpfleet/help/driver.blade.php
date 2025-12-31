@extends('sharpfleet.help.layout')

@php
    // Driver Help: define the Table of Contents structure in one place.
    // Each entry maps to a <section id="..." data-sf-help-section> below.
    $helpTitle = 'Driver Help';
    $helpIntro = 'Get started fast, then use the reference when you get stuck.';

    $helpSections = [
        [
            'id' => 'driver-getting-started',
            'title' => 'Getting Started',
            'children' => [
                ['id' => 'driver-gs-login', 'title' => 'Logging in'],
                ['id' => 'driver-gs-start', 'title' => 'Starting a trip'],
                ['id' => 'driver-gs-end', 'title' => 'Ending a trip'],
                ['id' => 'driver-gs-required', 'title' => 'What you may be asked to enter'],
                ['id' => 'driver-gs-offline', 'title' => 'Offline basics'],
            ],
        ],
        [
            'id' => 'driver-reference',
            'title' => 'Reference',
            'children' => [
                ['id' => 'driver-ref-trip-rules', 'title' => 'Trip rules in plain English'],
                ['id' => 'driver-ref-offline', 'title' => 'Offline trips & syncing'],
                ['id' => 'driver-ref-safety', 'title' => 'Safety checks'],
                ['id' => 'driver-ref-bookings', 'title' => 'Bookings (if enabled)'],
                ['id' => 'driver-ref-what-you-can-change', 'title' => 'What drivers can and can’t change'],
            ],
        ],
        [
            'id' => 'driver-faq',
            'title' => 'Common questions',
        ],
    ];
@endphp

@section('help-sections')

<section id="driver-getting-started" data-sf-help-section class="sf-help__section">
    <div class="sf-help__kicker sf-help__muted">Primary</div>
    <h2 class="sf-help__sectionTitle">Getting Started</h2>
    <p class="sf-help__lead">You don’t need to learn everything. Start a trip, end a trip, and you’re good. If something is required, SharpFleet will tell you.</p>

    <div class="sf-help__callout sf-help__callout--recommended">
        <div class="sf-help__calloutTitle">Recommended</div>
        <div>When in doubt: fill in the missing field message, then try again.</div>
    </div>
</section>

<section id="driver-gs-login" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Logging in</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">First time</div>
            <span class="sf-help__badge">1 minute</span>
        </div>
        <ol class="mb-0">
            <li>Open the invite email from your company.</li>
            <li>Tap the link and set your password.</li>
            <li>Log in with your email and password.</li>
        </ol>
        <div class="sf-help__callout" style="margin-top: 12px;">
            <div class="sf-help__calloutTitle">Tip</div>
            <div>Bookmark the page or add it to your home screen for quick access.</div>
        </div>
    </div>

    <details class="sf-help__details">
        <summary>Forgot your password?</summary>
        <ol class="mb-0" style="margin-top: 10px;">
            <li>On the login screen, tap <strong>Forgot password</strong>.</li>
            <li>Enter your email address.</li>
            <li>Follow the reset link in your email.</li>
        </ol>
        <div class="text-muted" style="margin-top: 8px;">If the email doesn’t arrive, check Junk/Spam.</div>
    </details>
</section>

<section id="driver-gs-start" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Starting a trip</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Step-by-step</div>
            <span class="sf-help__badge">Driver Dashboard</span>
        </div>
        <ol class="mb-0">
            <li>Open the <strong>Driver Dashboard</strong>.</li>
            <li>Select the <strong>Vehicle</strong> you are using.</li>
            <li>If you see it, choose the trip type (business/private).</li>
            <li>Fill in any required fields (examples: safety checks, customer, start time, start reading).</li>
            <li>Tap <strong>Start Trip</strong>.</li>
        </ol>
        <div class="sf-help__callout sf-help__callout--important">
            <div class="sf-help__calloutTitle">Important</div>
            <div>If you can’t find a vehicle in the list, it may be out of service or archived. Contact your admin.</div>
        </div>
    </div>
</section>

<section id="driver-gs-end" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Ending a trip</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Step-by-step</div>
            <span class="sf-help__badge">Keep it accurate</span>
        </div>
        <ol class="mb-0">
            <li>Open the Driver Dashboard.</li>
            <li>Find the active trip.</li>
            <li>Enter any required end details (end time, end reading).</li>
            <li>Tap <strong>End Trip</strong>.</li>
        </ol>
        <div class="sf-help__callout">
            <div class="sf-help__calloutTitle">Tip</div>
            <div>End the trip as soon as practical. It keeps availability and reports accurate.</div>
        </div>
    </div>

    <details class="sf-help__details">
        <summary>If you can’t end a trip</summary>
        <ul class="mb-0" style="margin-top: 10px;">
            <li>Check if an end reading or end time is required.</li>
            <li>If you’re offline, it may sync later when you’re back online.</li>
            <li>Refresh once you have signal again.</li>
        </ul>
    </details>
</section>

<section id="driver-gs-required" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">What you may be asked to enter</h3>
    <div class="sf-help__twoCol">
        <div class="sf-help__card">
            <div class="fw-bold mb-1">Common required items</div>
            <ul class="mb-0">
                <li>Safety checks</li>
                <li>Customer / client questions</li>
                <li>Start reading (km/hours)</li>
                <li>Manual start/end times</li>
            </ul>
        </div>
        <div class="sf-help__card">
            <div class="fw-bold mb-1">Why it changes</div>
            <div class="text-muted">Your company decides what is required. If a field is required, SharpFleet will block the trip until it’s completed.</div>
        </div>
    </div>
</section>

<section id="driver-gs-offline" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Offline basics</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Yes, it can still work</div>
            <span class="sf-help__badge">Limited features</span>
        </div>
        <ul class="mb-0">
            <li>If you’re offline, you may see an offline message.</li>
            <li>You can still capture trips with limited features.</li>
            <li>When you’re back online, keep the page open briefly so it can sync.</li>
        </ul>
    </div>
</section>

<section id="driver-reference" data-sf-help-section class="sf-help__section">
    <div class="sf-help__kicker sf-help__muted">Secondary</div>
    <h2 class="sf-help__sectionTitle">Reference</h2>
    <p class="sf-help__lead">Short answers in plain English.</p>
</section>

<section id="driver-ref-trip-rules" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Trip rules in plain English</h3>
    <details class="sf-help__details">
        <summary>Why do I see extra fields sometimes?</summary>
        <div class="text-muted" style="margin-top: 8px;">Your company turns rules on/off. Different companies ask for different details.</div>
        <ul class="mb-0" style="margin-top: 10px;">
            <li><strong>Manual times:</strong> you may need to enter a start/end time.</li>
            <li><strong>Readings:</strong> you may need to enter km/hours.</li>
            <li><strong>Locked readings:</strong> you may not be allowed to change a suggested reading.</li>
        </ul>
    </details>
</section>

<section id="driver-ref-offline" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Offline trips & syncing</h3>
    <details class="sf-help__details">
        <summary>What should I do when I’m back online?</summary>
        <ul class="mb-0" style="margin-top: 10px;">
            <li>Keep the page open for a moment.</li>
            <li>Refresh once if something doesn’t appear.</li>
        </ul>
        <div class="sf-help__callout" style="margin-top: 12px;">
            <div class="sf-help__calloutTitle">Tip</div>
            <div>If you see missing or duplicate trips after being offline, tell your admin the date/time and vehicle used.</div>
        </div>
    </details>
</section>

<section id="driver-ref-safety" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Safety checks</h3>
    <details class="sf-help__details">
        <summary>Why do I have to do a checklist?</summary>
        <div class="text-muted" style="margin-top: 8px;">Your company may require a quick safety check before starting a trip.</div>
        <div style="margin-top: 10px;">If it’s required, you can’t start until it’s completed.</div>
    </details>
</section>

<section id="driver-ref-bookings" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Bookings (if enabled)</h3>
    <details class="sf-help__details">
        <summary>What are bookings?</summary>
        <div class="text-muted" style="margin-top: 8px;">Some companies use bookings to reserve vehicles for certain times.</div>
        <div style="margin-top: 10px;">If a vehicle is booked or unavailable, you may be blocked from using it.</div>
    </details>
</section>

<section id="driver-ref-what-you-can-change" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">What drivers can and can’t change</h3>
    <div class="sf-help__twoCol">
        <div class="sf-help__card">
            <div class="fw-bold mb-1">You can usually</div>
            <ul class="mb-0">
                <li>Select a vehicle</li>
                <li>Start and end trips</li>
                <li>Enter the fields your company has enabled</li>
            </ul>
        </div>
        <div class="sf-help__card">
            <div class="fw-bold mb-1">You usually can’t</div>
            <ul class="mb-0">
                <li>Change company rules or settings</li>
                <li>Add/edit vehicles or users</li>
                <li>Override locked readings</li>
            </ul>
        </div>
    </div>
</section>

<section id="driver-faq" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Common questions</h2>

    <details class="sf-help__details">
        <summary>Why can’t I start a trip?</summary>
        <div class="text-muted" style="margin-top: 8px;">A required field is missing.</div>
        <div style="margin-top: 10px;">Scroll up, find the message, fill in the missing field, then try again.</div>
    </details>

    <details class="sf-help__details">
        <summary>Why can’t I find my vehicle in the list?</summary>
        <div class="text-muted" style="margin-top: 8px;">It may be out of service or archived.</div>
        <div style="margin-top: 10px;">Contact your admin to confirm which vehicle you should use.</div>
    </details>

    <details class="sf-help__details">
        <summary>Why is the start reading locked?</summary>
        <div class="text-muted" style="margin-top: 8px;">Your company may have turned off overrides.</div>
        <div style="margin-top: 10px;">If the reading looks wrong, contact your admin instead of guessing.</div>
    </details>

    <details class="sf-help__details">
        <summary>Do I need GPS for this?</summary>
        <div class="text-muted" style="margin-top: 8px;">No — SharpFleet does not assume GPS.</div>
        <div style="margin-top: 10px;">Trips are based on what you enter and your company’s rules.</div>
    </details>

    <details class="sf-help__details">
        <summary>Why don’t I see customer fields?</summary>
        <div class="text-muted" style="margin-top: 8px;">Your company may have customer capture turned off, or it may only show for certain trip types.</div>
    </details>

    <details class="sf-help__details">
        <summary>What if I’m offline?</summary>
        <div class="text-muted" style="margin-top: 8px;">You can still capture trips with limited features.</div>
        <div style="margin-top: 10px;">When you’re back online, keep the page open briefly so it can sync.</div>
    </details>
</section>

@endsection
