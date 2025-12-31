@extends('sharpfleet.help.layout')

@php
    // Admin Help: define the Table of Contents structure in one place.
    // Each entry maps to a <section id="..." data-sf-help-section> below.
    $helpTitle = 'Admin Help';
    $helpIntro = 'Short, practical guides for setting up your company and supporting drivers.';

    $helpSections = [
        [
            'id' => 'admin-getting-started',
            'title' => 'Getting started',
            'children' => [
                ['id' => 'admin-getting-started-first-login', 'title' => 'First login checklist'],
                ['id' => 'admin-getting-started-where-to-find-things', 'title' => 'Where to find things'],
            ],
        ],
        [
            'id' => 'admin-company-settings',
            'title' => 'Company settings & rules',
            'children' => [
                ['id' => 'admin-company-settings-trip-rules', 'title' => 'Trip rules'],
                ['id' => 'admin-company-settings-client-customer', 'title' => 'Client/customer capture'],
                ['id' => 'admin-company-settings-safety', 'title' => 'Safety checklist'],
                ['id' => 'admin-company-settings-timezone', 'title' => 'Timezone'],
            ],
        ],
        [
            'id' => 'admin-vehicles',
            'title' => 'Vehicles (add, edit, compliance)',
            'children' => [
                ['id' => 'admin-vehicles-add', 'title' => 'Add a vehicle'],
                ['id' => 'admin-vehicles-edit', 'title' => 'Edit vehicle details'],
                ['id' => 'admin-vehicles-out-of-service', 'title' => 'Mark a vehicle out of service'],
                ['id' => 'admin-vehicles-archive', 'title' => 'Archive a vehicle'],
                ['id' => 'admin-vehicles-compliance', 'title' => 'Compliance (rego & servicing)'],
            ],
        ],
        [
            'id' => 'admin-drivers',
            'title' => 'Drivers (add, invite, permissions)',
            'children' => [
                ['id' => 'admin-drivers-invite', 'title' => 'Invite a driver'],
                ['id' => 'admin-drivers-admin-as-driver', 'title' => 'Admin using Driver (Driver View)'],
                ['id' => 'admin-drivers-what-they-can-do', 'title' => 'What drivers can change'],
            ],
        ],
        [
            'id' => 'admin-trips',
            'title' => 'Trips & logbooks',
            'children' => [
                ['id' => 'admin-trips-how-trips-work', 'title' => 'How trips are recorded'],
                ['id' => 'admin-trips-common-issues', 'title' => 'Common trip issues'],
            ],
        ],
        [
            'id' => 'admin-reports',
            'title' => 'Reports & exports',
            'children' => [
                ['id' => 'admin-reports-trips', 'title' => 'Trip reports'],
                ['id' => 'admin-reports-csv', 'title' => 'CSV export'],
                ['id' => 'admin-reports-settings-driven', 'title' => 'Reports controlled by company settings'],
            ],
        ],
        [
            'id' => 'admin-trial',
            'title' => 'Trial vs paid behaviour',
            'children' => [
                ['id' => 'admin-trial-what-changes', 'title' => 'What changes when a trial expires'],
            ],
        ],
        [
            'id' => 'admin-faults',
            'title' => 'Incidents / faults',
            'children' => [
                ['id' => 'admin-faults-enabled', 'title' => 'Turning it on'],
                ['id' => 'admin-faults-review', 'title' => 'Reviewing and archiving'],
            ],
        ],
        [
            'id' => 'admin-faq',
            'title' => 'Common admin questions',
        ],
    ];
@endphp

@section('help-sections')

{{--
  NOTE: Keep each section short and scannable.
  Use simple steps, plain language, and call out what is controlled by company settings.
--}}

<section id="admin-getting-started" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Getting started</h2>
    <p class="text-muted">Use this checklist to get set up quickly. Most driver behaviour is controlled by your Company Settings.</p>
</section>

<section id="admin-getting-started-first-login" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">First login checklist</h3>
    <ul>
        <li><strong>Check your company profile:</strong> Go to <em>Company</em> and confirm your details are correct.</li>
        <li><strong>Set your timezone:</strong> This controls how times show on trips, bookings, faults, and reports.</li>
        <li><strong>Add your vehicles:</strong> Add at least one vehicle before inviting drivers.</li>
        <li><strong>Choose your trip rules:</strong> Decide what drivers must enter when starting/ending trips (examples: odometer reading, safety checks, manual start/end time).</li>
        <li><strong>Invite drivers:</strong> Send invites so drivers can log in and start trips.</li>
        <li><strong>Run a test trip:</strong> Use <strong>Driver</strong> (Driver View) to confirm your rules make sense in real use.</li>
    </ul>
</section>

<section id="admin-getting-started-where-to-find-things" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Where to find things</h3>
    <ul>
        <li><strong>Fleet:</strong> Dashboard, Vehicles, and Bookings.</li>
        <li><strong>Operations:</strong> Faults, Reminders, and Safety Checks.</li>
        <li><strong>Customers:</strong> Your customer list (if enabled).</li>
        <li><strong>Reports:</strong> Trip Reports and exports.</li>
        <li><strong>Company:</strong> Company Overview, Edit Company Details, Users/Drivers, and Company Settings.</li>
    </ul>
    <p class="text-muted">Tip: Some “Company actions” that used to appear on the Company page are now available from the <strong>Company</strong> dropdown in the top navigation.</p>
</section>

<section id="admin-company-settings" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Company settings & rules</h2>
    <p class="text-muted">These settings decide what drivers see and what they must enter. If a field is hidden or locked for drivers, it is usually because of a company setting.</p>
</section>

<section id="admin-company-settings-trip-rules" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Trip rules</h3>
    <p><strong>Trip rules</strong> control what a driver must enter when starting and ending a trip.</p>
    <ul>
        <li><strong>Odometer / start reading required:</strong> If enabled, drivers must enter a starting reading (or SharpFleet may fill the last known reading depending on your setup).</li>
        <li><strong>Odometer override allowed:</strong> If disabled, drivers cannot change the starting reading away from the last known reading.</li>
        <li><strong>Private trips allowed:</strong> If enabled, drivers can mark a trip as private. If disabled, all trips are treated as business.</li>
        <li><strong>Require manual start/end times:</strong> If enabled, drivers must enter the start time (and end time) for each trip. This is useful when trips are logged after the fact.</li>
    </ul>
    <p class="text-muted">Tip: Choose the simplest rules your business needs. More required fields usually means more “forgot to enter it” mistakes.</p>
</section>

<section id="admin-company-settings-client-customer" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Client/customer capture</h3>
    <p>SharpFleet can capture “client/customer” details on business trips, depending on your settings.</p>
    <ul>
        <li><strong>Client presence:</strong> You can ask drivers “Was a client present?” and (optionally) make it required before starting a business trip.</li>
        <li><strong>Client label:</strong> You can rename the word drivers see (example: “Customer”, “Client”, “Passenger”).</li>
        <li><strong>Customer selection / entry:</strong> You can let drivers choose from your customer list and/or type a name manually.</li>
    </ul>
    <p class="text-muted">Important: If you make a field required, drivers will be blocked from starting a trip until it’s completed.</p>
</section>

<section id="admin-company-settings-safety" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Safety checklist</h3>
    <p>If enabled, drivers will be asked to complete a short checklist before starting a trip.</p>
    <ul>
        <li>Go to <strong>Safety Checks</strong> to define the checklist items.</li>
        <li>Turn the feature on in <strong>Settings</strong> (and make it required if your process needs it).</li>
        <li>Keep the list short. Drivers are more likely to complete a checklist that takes under a minute.</li>
    </ul>
</section>

<section id="admin-company-settings-timezone" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Timezone</h3>
    <p>Timezone affects how dates and times are displayed to you and your drivers.</p>
    <ul>
        <li>Trip start/end times display in your company timezone.</li>
        <li>Bookings and conflicts are checked using the same timezone rules.</li>
        <li>Reports show timestamps in the company timezone.</li>
    </ul>
</section>

<section id="admin-vehicles" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Vehicles (add, edit, compliance)</h2>
    <p class="text-muted">Vehicles are the core of SharpFleet. Add them first, then invite drivers.</p>
</section>

<section id="admin-vehicles-add" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Add a vehicle</h3>
    <ol>
        <li>Go to <strong>Vehicles</strong>.</li>
        <li>Select <strong>Add vehicle</strong>.</li>
        <li>Enter a clear name (example: “Hilux 1”) and the registration number.</li>
        <li>Select the tracking mode used by your business (distance or hours) if available.</li>
        <li>Save.</li>
    </ol>
</section>

<section id="admin-vehicles-edit" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Edit vehicle details</h3>
    <p>Use <strong>Edit</strong> to update names, registration numbers, and any compliance dates/readings you track.</p>
    <p class="text-muted">Tip: Keep vehicle names consistent so reports are easy to read.</p>
</section>

<section id="admin-vehicles-out-of-service" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Mark a vehicle out of service</h3>
    <p>Use this when a vehicle is temporarily unavailable (for example: service, repair, accident, or inspection).</p>
    <ol>
        <li>Go to <strong>Vehicles</strong>.</li>
        <li>Open the vehicle and select <strong>Edit</strong>.</li>
        <li>In <strong>Service Status</strong>, tick <strong>Mark vehicle as out of service</strong>.</li>
        <li>Select a <strong>Reason</strong> and add a short <strong>Note</strong> (example: location or workshop).</li>
        <li>Save.</li>
    </ol>
    <p class="text-muted">Out-of-service vehicles cannot be booked by drivers and cannot be used to start a trip.</p>
</section>

<section id="admin-vehicles-archive" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Archive a vehicle</h3>
    <p>Archiving hides a vehicle from day-to-day use, without deleting history.</p>
    <ol>
        <li>Go to <strong>Vehicles</strong>.</li>
        <li>Open the vehicle and select <strong>Archive</strong>.</li>
        <li>The vehicle will no longer be selectable for new trips.</li>
    </ol>
    <p class="text-muted">Tip: If a vehicle is only temporarily unavailable, use <strong>Out of service</strong> instead of archiving.</p>
</section>

<section id="admin-vehicles-compliance" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Compliance (rego & servicing)</h3>
    <p>SharpFleet can track compliance items if you enable them in settings.</p>
    <ul>
        <li><strong>Registration tracking:</strong> record rego expiry dates.</li>
        <li><strong>Servicing tracking:</strong> record service due dates and/or service due readings.</li>
    </ul>
    <p class="text-muted">If you do not see these fields, they may be turned off in your Company Settings.</p>
</section>

<section id="admin-drivers" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Drivers (add, invite, permissions)</h2>
    <p class="text-muted">Drivers use SharpFleet to start and end trips. You control what they can edit through your settings.</p>
</section>

<section id="admin-drivers-invite" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Invite a driver</h3>
    <ol>
        <li>Go to <strong>Users</strong>.</li>
        <li>Select <strong>Invite</strong>.</li>
        <li>Enter the driver’s name and email address.</li>
        <li>Send the invite. The driver will receive an email with a link to set their password.</li>
    </ol>
    <p class="text-muted">If the driver can’t find the email, ask them to check Junk/Spam, then resend the invite from the Users page.</p>
</section>

<section id="admin-drivers-admin-as-driver" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Admin using Driver (Driver View)</h3>
    <p>Admins can switch to <strong>Driver</strong> (Driver View) to see the driver dashboard. This is useful for testing your settings.</p>
    <ul>
        <li>Use it to confirm required fields (like safety checks or client presence) are practical.</li>
        <li>Use it to understand what drivers will see on mobile.</li>
    </ul>
</section>

<section id="admin-drivers-what-they-can-do" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">What drivers can change</h3>
    <p>Drivers can usually:</p>
    <ul>
        <li>Select a vehicle.</li>
        <li>Start and end trips.</li>
        <li>Enter trip details that you have enabled (examples: customer name, client presence, odometer readings).</li>
    </ul>
    <p>Drivers usually cannot:</p>
    <ul>
        <li>Change company rules.</li>
        <li>Add or edit vehicles (unless you specifically give them admin access).</li>
        <li>Change certain readings if you have disabled overrides (example: odometer override disabled).</li>
    </ul>
</section>

<section id="admin-trips" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Trips & logbooks</h2>
    <p class="text-muted">Trips are recorded when drivers start and end a trip. The exact fields depend on your company settings.</p>
</section>

<section id="admin-trips-how-trips-work" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">How trips are recorded</h3>
    <ul>
        <li><strong>Start:</strong> the driver selects a vehicle and enters any required start details.</li>
        <li><strong>End:</strong> the driver ends the trip and enters any required end details.</li>
        <li><strong>Private vs business:</strong> if enabled, the driver can choose private. If disabled, trips are treated as business.</li>
        <li><strong>Distance vs hours:</strong> some vehicles track kilometres, others track hours. Reports calculate totals based on the vehicle’s tracking mode.</li>
    </ul>
    <p class="text-muted">Important: SharpFleet does not assume GPS tracking. Trips are based on what the driver enters and what your rules require.</p>
</section>

<section id="admin-trips-common-issues" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Common trip issues</h3>
    <ul>
        <li><strong>Driver can’t start a trip:</strong> check which fields are required in Settings (odometer required, client presence required, safety checklist required, manual times required).</li>
        <li><strong>Driver entered the wrong reading:</strong> if overrides are disabled, the system may block changes. Decide whether you want overrides enabled.</li>
        <li><strong>Missing end trip:</strong> ask the driver to end the trip as soon as possible. Reports will show incomplete trips until ended.</li>
    </ul>
</section>

<section id="admin-reports" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Reports & exports</h2>
    <p class="text-muted">Reports are designed for exporting and payroll/logbook workflows. What filters are available can be controlled by company settings.</p>
</section>

<section id="admin-reports-trips" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Trip reports</h3>
    <ol>
        <li>Go to <strong>Reports</strong>.</li>
        <li>Review the <strong>Applied settings</strong> box at the top. This tells you what rules are currently applied (date range, private trips, and whether filters are locked).</li>
        <li>If filters are available, select a vehicle, date range, and/or customer.</li>
        <li>Select <strong>Filter</strong> to refresh the results.</li>
    </ol>
</section>

<section id="admin-reports-csv" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">CSV export</h3>
    <p>Use <strong>Export CSV</strong> to download the same results you’re viewing.</p>
    <ul>
        <li>The export includes key trip details and timestamps.</li>
        <li>If filters are locked by company settings, the export will follow those locked rules.</li>
    </ul>
</section>

<section id="admin-reports-settings-driven" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Reports controlled by company settings</h3>
    <p>Some companies want reports to always follow set rules (for example, fixed date ranges or always excluding private trips).</p>
    <p>If a filter looks disabled, that is expected behaviour: it means your company settings have locked that filter.</p>
</section>

<section id="admin-trial" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Trial vs paid behaviour</h2>
    <p class="text-muted">SharpFleet can restrict actions when a trial expires. This is designed to prevent changes, while still allowing access to existing information.</p>
</section>

<section id="admin-trial-what-changes" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">What changes when a trial expires</h3>
    <ul>
        <li>You may be able to <strong>view</strong> information (like reports) but be blocked from <strong>changing</strong> setup (like adding vehicles or inviting drivers).</li>
        <li>Drivers may be blocked from starting new trips, depending on your company’s access rules.</li>
        <li>If something is locked, SharpFleet will show a “trial expired” message and provide the allowed next steps.</li>
    </ul>
    <p class="text-muted">If your team needs to keep using SharpFleet, speak to your account owner or manager about the next step for your company.</p>
</section>

<section id="admin-faults" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Incidents / faults</h2>
    <p class="text-muted">Drivers can report incidents/faults if your company has enabled the feature.</p>
</section>

<section id="admin-faults-enabled" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Turning it on</h3>
    <ul>
        <li>If you can’t see the Faults page, the feature may be turned off for your company.</li>
        <li>Once enabled, drivers can submit faults during a trip or from their dashboard (depending on your setup).</li>
    </ul>
</section>

<section id="admin-faults-review" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Reviewing and archiving</h3>
    <ul>
        <li>Go to <strong>Faults</strong> to see new items.</li>
        <li>Update the status as you review and action items.</li>
        <li>Use <strong>Archive</strong> to move completed items out of the main working list while keeping a record.</li>
        <li>Faults can be categorised (example: “Vehicle Issue” vs “Vehicle Accident”) to help with reporting and follow-up.</li>
    </ul>
</section>

<section id="admin-faq" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Common admin questions</h2>

    <h3 class="sf-help__sectionTitle">“Why are some fields missing on the driver screen?”</h3>
    <p>Because your company settings control what drivers see. Check <strong>Settings</strong> for trip rules, client presence, and safety checks.</p>

    <h3 class="sf-help__sectionTitle">“Why can’t a driver change a reading?”</h3>
    <p>If you disabled overrides, SharpFleet will block changes to keep readings consistent. If you want drivers to correct mistakes, enable overrides.</p>

    <h3 class="sf-help__sectionTitle">“Why are filters disabled on Reports?”</h3>
    <p>Your reporting settings may lock filters (for example, fixed date range or locked vehicle/customer). The “Applied settings” box explains what’s locked.</p>

    <h3 class="sf-help__sectionTitle">“Do we need GPS tracking for trips?”</h3>
    <p>No. SharpFleet does not assume GPS. Trips are logged based on what the driver enters and what your company rules require.</p>
</section>

@endsection
