@extends('sharpfleet.help.layout')

@php
    // Driver Help: define the Table of Contents structure in one place.
    // Each entry maps to a <section id="..." data-sf-help-section> below.
    $helpTitle = 'Driver Help';
    $helpIntro = 'Quick instructions for starting and ending trips, including offline use.';

    $helpSections = [
        [
            'id' => 'driver-login',
            'title' => 'Logging in',
            'children' => [
                ['id' => 'driver-login-first-time', 'title' => 'First time setup'],
                ['id' => 'driver-login-forgot', 'title' => 'Forgot your password'],
            ],
        ],
        [
            'id' => 'driver-start-trip',
            'title' => 'Starting a trip',
            'children' => [
                ['id' => 'driver-start-trip-steps', 'title' => 'Step-by-step'],
                ['id' => 'driver-start-trip-required-fields', 'title' => 'Fields that may be required'],
            ],
        ],
        [
            'id' => 'driver-end-trip',
            'title' => 'Ending a trip',
            'children' => [
                ['id' => 'driver-end-trip-steps', 'title' => 'Step-by-step'],
                ['id' => 'driver-end-trip-common-issues', 'title' => 'Common issues'],
            ],
        ],
        [
            'id' => 'driver-corrections',
            'title' => 'Correcting mistakes',
            'children' => [
                ['id' => 'driver-corrections-readings', 'title' => 'Wrong reading entered'],
                ['id' => 'driver-corrections-times', 'title' => 'Wrong time entered'],
                ['id' => 'driver-corrections-missing-end', 'title' => 'Forgot to end a trip'],
            ],
        ],
        [
            'id' => 'driver-client-job',
            'title' => 'Client/job entry',
            'children' => [
                ['id' => 'driver-client-job-labels', 'title' => 'Customer vs client wording'],
                ['id' => 'driver-client-job-customer', 'title' => 'Selecting or typing a customer'],
                ['id' => 'driver-client-job-presence', 'title' => 'Client presence question'],
            ],
        ],
        [
            'id' => 'driver-offline',
            'title' => 'Offline / PWA usage',
            'children' => [
                ['id' => 'driver-offline-install', 'title' => 'Install the app (optional)'],
                ['id' => 'driver-offline-capture', 'title' => 'Capturing trips offline'],
                ['id' => 'driver-offline-sync', 'title' => 'Syncing when back online'],
            ],
        ],
        [
            'id' => 'driver-what-you-can-change',
            'title' => 'What drivers can and cannot change',
        ],
        [
            'id' => 'driver-faq',
            'title' => 'Common driver questions',
        ],
    ];
@endphp

@section('help-sections')

<section id="driver-login" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Logging in</h2>
    <p class="text-muted">You can use SharpFleet in a browser on your phone, or install it like an app (optional).</p>
</section>

<section id="driver-login-first-time" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">First time setup</h3>
    <ol>
        <li>Open the invite email from your company.</li>
        <li>Tap the link and set your password.</li>
        <li>Log in using your email and password.</li>
        <li>Bookmark the page or add it to your home screen (see Offline/PWA section).</li>
    </ol>
</section>

<section id="driver-login-forgot" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Forgot your password</h3>
    <ol>
        <li>On the login screen, tap <strong>Forgot password</strong>.</li>
        <li>Enter your email address.</li>
        <li>Check your email and follow the reset link.</li>
        <li>Set a new password and log in.</li>
    </ol>
    <p class="text-muted">If you don’t receive the email within a few minutes, check Junk/Spam and confirm you typed the correct email address.</p>
</section>

<section id="driver-start-trip" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Starting a trip</h2>
    <p class="text-muted">What you must fill in can change depending on your company’s rules. If something is required, SharpFleet will block the trip until it’s completed.</p>
</section>

<section id="driver-start-trip-steps" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step-by-step</h3>
    <ol>
        <li>Open the <strong>Driver Dashboard</strong>.</li>
        <li>Select the <strong>Vehicle</strong> you are using.</li>
        <li class="text-muted">If you can’t find a vehicle in the list, it may be out of service or archived. Contact your admin.</li>
        <li>If your company requires it, select a <strong>Start time</strong> (manual time entry).</li>
        <li>Select the <strong>Trip type</strong> (business or private) if you see the option.</li>
        <li>If shown, answer the <strong>client/customer</strong> questions (example: “Was a customer present?”).</li>
        <li>Enter the <strong>starting reading</strong> (kilometres or hours) if required.</li>
        <li>Tap <strong>Start Trip</strong>.</li>
    </ol>
</section>

<section id="driver-start-trip-required-fields" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Fields that may be required</h3>
    <ul>
        <li><strong>Safety checklist:</strong> you may need to tick off a short checklist before starting.</li>
        <li><strong>Client presence:</strong> you may need to answer Yes/No before a business trip can start.</li>
        <li><strong>Customer name:</strong> you may need to select a customer or type a name (depending on your company’s setup).</li>
        <li><strong>Starting reading:</strong> you may need to enter a start reading. In some companies, you may not be allowed to change the suggested reading.</li>
        <li><strong>Manual start time:</strong> some companies require you to enter the start time (and end time) yourself.</li>
    </ul>
</section>

<section id="driver-end-trip" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Ending a trip</h2>
    <p class="text-muted">End the trip as soon as practical. This keeps vehicle availability and reports accurate.</p>
</section>

<section id="driver-end-trip-steps" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step-by-step</h3>
    <ol>
        <li>Open the driver dashboard.</li>
        <li>Find the active trip.</li>
        <li>If your company requires it, select an <strong>End time</strong> (manual time entry).</li>
        <li>Enter the <strong>ending reading</strong> (kilometres or hours) if required.</li>
        <li>Tap <strong>End Trip</strong>.</li>
    </ol>
</section>

<section id="driver-end-trip-common-issues" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Common issues</h3>
    <ul>
        <li><strong>“I can’t end the trip”:</strong> check if the end reading or end time is required.</li>
        <li><strong>“The reading looks wrong”:</strong> some companies lock readings to prevent mistakes. If it looks wrong, contact your admin.</li>
        <li><strong>“I ended the trip but it still shows active”:</strong> refresh the page. If you’re offline, it may sync later.</li>
    </ul>
</section>

<section id="driver-corrections" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Correcting mistakes</h2>
    <p class="text-muted">What you can correct depends on your company settings. If you can’t change something, contact your admin.</p>
</section>

<section id="driver-corrections-readings" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Wrong reading entered</h3>
    <ul>
        <li>If you notice before submitting, correct it and continue.</li>
        <li>If the field is locked, you may not be allowed to override it. Contact your admin.</li>
        <li>If you already submitted the wrong reading, tell your admin as soon as possible so they can advise the next step.</li>
    </ul>
</section>

<section id="driver-corrections-times" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Wrong time entered</h3>
    <p>If your company uses manual time entry, choose the correct start/end time before submitting.</p>
    <p class="text-muted">If you already submitted incorrect times and you can’t edit them, contact your admin.</p>
</section>

<section id="driver-corrections-missing-end" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Forgot to end a trip</h3>
    <ol>
        <li>Open SharpFleet as soon as you can.</li>
        <li>End the trip and enter the correct ending details.</li>
        <li>If your company requires manual end time, select the actual end time.</li>
    </ol>
</section>

<section id="driver-client-job" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Client/job entry</h2>
    <p class="text-muted">Some companies capture customer/client details for business trips. If you do not see these fields, your company may have them turned off.</p>
</section>

<section id="driver-client-job-labels" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Customer vs client wording</h3>
    <p>The wording can be different depending on your company. For example, you may see “Client”, “Customer”, or “Passenger”.</p>
</section>

<section id="driver-client-job-customer" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Selecting or typing a customer</h3>
    <ul>
        <li>If you see a dropdown list, you can select an existing customer.</li>
        <li>If you see a text box, you can type a customer name (if your company allows it).</li>
        <li>If you don’t see customer fields at all, your company may have customer capture turned off.</li>
    </ul>
</section>

<section id="driver-client-job-presence" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Client presence question</h3>
    <p>Some companies require a Yes/No answer for whether a client/customer was present.</p>
    <p class="text-muted">If it’s required, you can’t start the trip until you choose an answer.</p>
</section>

<section id="driver-offline" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Offline / PWA usage</h2>
    <p class="text-muted">SharpFleet can keep working with limited features when your phone has no signal. When you’re back online, it will try to sync.</p>
</section>

<section id="driver-offline-install" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Install the app (optional)</h3>
    <ul>
        <li>On iPhone: open SharpFleet in Safari, tap <strong>Share</strong>, then tap <strong>Add to Home Screen</strong>.</li>
        <li>On Android (Chrome): open the menu and tap <strong>Add to Home screen</strong> (or <strong>Install app</strong>).</li>
    </ul>
</section>

<section id="driver-offline-capture" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Capturing trips offline</h3>
    <ul>
        <li>If you’re offline, SharpFleet may show an “offline” message.</li>
        <li>Enter trip details as normal. Some actions may be limited until you’re back online.</li>
        <li>If your company requires manual start/end times, you may need to enter times while offline too.</li>
    </ul>
</section>

<section id="driver-offline-sync" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Syncing when back online</h3>
    <ul>
        <li>When your phone is online again, keep the page open for a moment.</li>
        <li>SharpFleet will try to sync offline trips automatically.</li>
        <li>If something doesn’t appear, refresh the page once you’re online.</li>
    </ul>
    <p class="text-muted">Tip: If you see duplicate trips or missing trips after being offline, report it to your admin with the date/time and vehicle used.</p>
</section>

<section id="driver-what-you-can-change" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">What drivers can and cannot change</h2>
    <p>Drivers can usually:</p>
    <ul>
        <li>Select a vehicle.</li>
        <li>Start and end trips.</li>
        <li>Enter the fields your company has enabled (customer/client details, presence, readings, times).</li>
    </ul>

    <p>Drivers usually cannot:</p>
    <ul>
        <li>Change company rules or settings.</li>
        <li>Add/edit vehicles or users.</li>
        <li>Override locked readings (if your company has disabled overrides).</li>
    </ul>
</section>

<section id="driver-faq" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Common driver questions</h2>

    <h3 class="sf-help__sectionTitle">“Why can’t I start a trip?”</h3>
    <p>One of the required fields is missing (for example: safety checklist, client presence, start reading, or start time). Scroll up and look for a message, then complete the missing field.</p>

    <h3 class="sf-help__sectionTitle">“Why can’t I find my vehicle in the list?”</h3>
    <p>Your admin may have marked the vehicle <strong>out of service</strong> (service/repair/inspection), or it may be archived. If you’re not sure, contact your admin and confirm which vehicle you should use.</p>

    <h3 class="sf-help__sectionTitle">“Why is the start reading locked?”</h3>
    <p>Your company may have turned off overrides. If the reading looks wrong, contact your admin instead of guessing.</p>

    <h3 class="sf-help__sectionTitle">“Do I need GPS for this?”</h3>
    <p>No. SharpFleet does not assume GPS tracking. Trips are based on what you enter and your company’s rules.</p>

    <h3 class="sf-help__sectionTitle">“Why don’t I see customer fields?”</h3>
    <p>Your company may have customer capture turned off for drivers, or it may only show for certain trip types.</p>

    <h3 class="sf-help__sectionTitle">“What if I’m offline?”</h3>
    <p>You can still capture trips with limited features. When you’re back online, keep the page open briefly so it can sync.</p>
</section>

@endsection
