@extends('sharpfleet.help.layout')

@php
    // Admin Help: define the Table of Contents structure in one place.
    // Each entry maps to a <section id="..." data-sf-help-section> below.
    $helpTitle = 'Admin Help';
    $helpIntro = 'A quick setup guide first, then a reference you can search.';

    $helpSections = [
        [
            'id' => 'admin-setup',
            'title' => 'Setup Guide',
            'children' => [
                ['id' => 'admin-setup-checklist', 'title' => 'First-time checklist'],
                ['id' => 'admin-setup-rerun', 'title' => 'Re-run setup wizard (testing)'],
                ['id' => 'admin-setup-step-company', 'title' => 'Step 1 — Company details'],
                ['id' => 'admin-setup-step-presence', 'title' => 'Step 2 — Passenger/client presence'],
                ['id' => 'admin-setup-step-trip-rules', 'title' => 'Step 3 — Trip rules'],
                ['id' => 'admin-setup-step-vehicle-tracking', 'title' => 'Step 4 — Vehicle tracking'],
                ['id' => 'admin-setup-step-reminders', 'title' => 'Step 5 — Reminder emails'],
                ['id' => 'admin-setup-step-client-addresses', 'title' => 'Step 6 — Client addresses (optional)'],
                ['id' => 'admin-setup-step-safety', 'title' => 'Step 7 — Safety checks (optional)'],
                ['id' => 'admin-setup-step-incident', 'title' => 'Step 8 — Vehicle issue/accident reporting (optional)'],
                ['id' => 'admin-setup-step-finish', 'title' => 'Step 9 — Finish setup'],
                ['id' => 'admin-setup-next-vehicles', 'title' => 'Next — Add vehicles'],
                ['id' => 'admin-setup-step-invite', 'title' => 'Next — Invite drivers'],
                ['id' => 'admin-setup-step-test-trip', 'title' => 'Next — Run a test trip'],
            ],
        ],
        [
            'id' => 'admin-reference',
            'title' => 'Reference',
            'children' => [
                ['id' => 'admin-ref-company-settings', 'title' => 'Company settings & rules'],
                ['id' => 'admin-ref-trip-rules', 'title' => 'Trip rules'],
                ['id' => 'admin-ref-client-customer', 'title' => 'Client / customer capture'],
                ['id' => 'admin-ref-safety', 'title' => 'Safety checks'],
                ['id' => 'admin-ref-vehicles', 'title' => 'Vehicles & maintenance'],
                ['id' => 'admin-ref-reports', 'title' => 'Reports & exports'],
            ],
        ],
        [
            'id' => 'admin-faq',
            'title' => 'Common questions',
        ],
    ];
@endphp

@section('help-sections')

{{--
  NOTE: Keep each section short and scannable.
  Use simple steps, plain language, and call out what is controlled by company settings.
--}}

<section id="admin-setup" data-sf-help-section class="sf-help__section">
    <div class="sf-help__kicker sf-help__muted">Primary</div>
    <h2 class="sf-help__sectionTitle">Setup Guide</h2>
    <p class="sf-help__lead">Follow this once when you’re setting up a new company. Keep it simple. You can refine rules later.</p>

    <div class="sf-help__callout sf-help__callout--recommended">
        <div class="sf-help__calloutTitle">Recommended</div>
        <div>Do the setup in this order. It prevents driver confusion and reduces “can’t start trip” issues.</div>
    </div>
</section>

<section id="admin-setup-rerun" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Re-run setup wizard (testing)</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Restart onboarding</div>
            <span class="sf-help__badge">Admin</span>
        </div>
        <p class="mb-2">This is for testing. It resets the “setup complete” flag and sends you back to Step 1.</p>
        <div class="sf-help__callout sf-help__callout--important">
            <div class="sf-help__calloutTitle">Important</div>
            <div>Admin pages will require completing setup again until you finish Step 9.</div>
        </div>
        <div class="sf-help__actions">
            <form method="POST" action="/app/sharpfleet/admin/setup/rerun" onsubmit="return confirm('Re-run the setup wizard? Admin pages will require completing setup again.');">
                @csrf
                <button class="btn btn-secondary btn-sm" type="submit">Re-run setup wizard</button>
            </form>
        </div>
    </div>
</section>

<section id="admin-setup-checklist" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">First-time checklist</h3>

    <div class="sf-help__twoCol">
        <div class="sf-help__card">
            <div class="sf-help__cardHeader">
                <div class="fw-bold">Your setup path</div>
                <span class="sf-help__badge">~10–20 minutes</span>
            </div>
            <ol class="mb-0">
                <li>Company details</li>
                <li>Passenger/client presence</li>
                <li>Trip rules</li>
                <li>Vehicle tracking</li>
                <li>Reminder emails</li>
                <li>Client addresses (optional)</li>
                <li>Safety checks (optional)</li>
                <li>Vehicle issue/accident reporting (optional)</li>
                <li>Finish setup</li>
                <li>Add vehicles</li>
                <li>Invite drivers</li>
                <li>Run a test trip</li>
            </ol>
        </div>

        <div class="sf-help__card">
            <div class="sf-help__cardHeader">
                <div class="fw-bold">Where to find things</div>
                <span class="sf-help__badge">Top navigation</span>
            </div>
            <ul class="mb-0">
                <li><strong>Fleet:</strong> Dashboard, Vehicles, Bookings</li>
                <li><strong>Operations:</strong> Faults, Reminders, Safety Checks</li>
                <li><strong>Reports:</strong> Trip Reports</li>
                <li><strong>Company:</strong> Company Overview, Edit Company Details, Users/Drivers, Company Settings</li>
            </ul>
            <div class="text-muted" style="margin-top: 8px;">Some company actions that used to show on the Company page are now in the <strong>Company</strong> dropdown.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-company" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 1 — Company details</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Set company name and time zone</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">Set your company name, time zone, and (optionally) industry. This affects how times show across trips, faults, bookings, and reports.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/company">Open Step 1</a>
        </div>
        <div class="sf-help__callout">
            <div class="sf-help__calloutTitle">Why it matters</div>
            <div>Getting the time zone right prevents confusing trip times and report timestamps later.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-presence" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 2 — Passenger/client presence</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Decide what drivers must record</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">Choose whether drivers must record if a passenger/client was present, and (optionally) capture a customer name for billing/reporting.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/settings/presence">Open Step 2</a>
        </div>
        <div class="sf-help__callout sf-help__callout--important">
            <div class="sf-help__calloutTitle">Important</div>
            <div>If you make it required, drivers will be blocked from starting a trip until they answer it.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-trip-rules" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 3 — Trip rules</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Choose what drivers must enter</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">Trip rules decide what shows on the driver screen. If something is required, drivers can’t start a trip until it’s filled in.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/settings/trip-rules">Open Step 3</a>
        </div>
        <div class="sf-help__callout sf-help__callout--recommended">
            <div class="sf-help__calloutTitle">Recommended</div>
            <div>Start with the minimum required fields. You can tighten rules later once everyone is comfortable.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-vehicle-tracking" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 4 — Vehicle tracking</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Choose what admin-managed vehicle details you track</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">Turn on only what you plan to maintain (rego and servicing). These settings control which fields appear on vehicle profiles and which reminders can be used.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/settings/vehicle-tracking">Open Step 4</a>
        </div>
        <div class="sf-help__callout">
            <div class="sf-help__calloutTitle">Tip</div>
            <div>If you enable rego tracking, you’ll see rego fields when creating/editing vehicles.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-reminders" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 5 — Reminder emails</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Set your “due soon” windows</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">These thresholds control when vehicles are marked as due soon and when reminder emails are sent. They apply at a company level (not per vehicle).</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/settings/reminders">Open Step 5</a>
        </div>
        <div class="sf-help__callout">
            <div class="sf-help__calloutTitle">Example</div>
            <div>30 days means vehicles become “Due soon” 30 days before the due date.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-client-addresses" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 6 — Client addresses (optional)</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Decide if you need address capture</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">Enable address capture only if you need it for billing or job tracking. Leaving it off reduces sensitive data captured by drivers.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/settings/client-addresses">Open Step 6</a>
        </div>
    </div>
</section>

<section id="admin-setup-step-safety" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 7 — Safety checks (optional)</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Set up a quick checklist</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">If you enable safety checks, drivers will be prompted to complete them before starting a trip.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/settings/safety-check">Open Step 7</a>
            <a class="btn btn-secondary btn-sm" href="/app/sharpfleet/admin/safety-checks">Manage checklist</a>
        </div>
        <div class="sf-help__callout">
            <div class="sf-help__calloutTitle">Tip</div>
            <div>Keep it short. Drivers are more likely to complete a checklist that takes under a minute.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-incident" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 8 — Vehicle issue/accident reporting (optional)</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Enable incident reporting if you need it</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">Enable this if drivers need to report vehicle issues or accidents. It adds a reporting option in the driver portal.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/settings/incident-reporting">Open Step 8</a>
        </div>
    </div>
</section>

<section id="admin-setup-step-finish" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Step 9 — Finish setup</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Complete onboarding</div>
            <span class="sf-help__badge">Setup Wizard</span>
        </div>
        <p class="mb-2">Finish setup to unlock the rest of the admin area. You can change settings later under Company Settings.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/setup/finish">Open Step 9</a>
        </div>
    </div>
</section>

<section id="admin-setup-next-vehicles" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Next — Add vehicles</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Add your vehicles</div>
            <span class="sf-help__badge">Vehicles</span>
        </div>
        <p class="mb-2">Add at least one vehicle before inviting drivers. Use clear names (example: “Hilux 1”).</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/vehicles">Open Vehicles</a>
        </div>
        <div class="sf-help__callout">
            <div class="sf-help__calloutTitle">Tip</div>
            <div>If a vehicle is temporarily unavailable, mark it <strong>out of service</strong> instead of archiving it.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-invite" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Next — Invite drivers</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Invite your team</div>
            <span class="sf-help__badge">Users/Drivers</span>
        </div>
        <p class="mb-2">Send an invite so drivers can set a password and start using SharpFleet.</p>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/users">Open Users</a>
            <a class="btn btn-secondary btn-sm" href="/app/sharpfleet/admin/users/invite">Invite a driver</a>
        </div>
        <div class="sf-help__callout">
            <div class="sf-help__calloutTitle">Tip</div>
            <div>If someone can’t find the invite, ask them to check Junk/Spam, then resend from Users.</div>
        </div>
    </div>
</section>

<section id="admin-setup-step-test-trip" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Next — Run a test trip</h3>
    <div class="sf-help__card">
        <div class="sf-help__cardHeader">
            <div class="fw-bold">Test the driver experience</div>
            <span class="sf-help__badge">Driver</span>
        </div>
        <p class="mb-2">Use <strong>Driver</strong> (Driver View) to confirm your rules make sense in real use.</p>
        <div class="sf-help__callout sf-help__callout--important">
            <div class="sf-help__calloutTitle">Important</div>
            <div>If drivers get blocked from starting trips, it’s usually a required field in Company Settings.</div>
        </div>
    </div>
</section>

<section id="admin-reference" data-sf-help-section class="sf-help__section">
    <div class="sf-help__kicker sf-help__muted">Secondary</div>
    <h2 class="sf-help__sectionTitle">Reference</h2>
    <p class="sf-help__lead">Short answers to common questions. Use the search box to jump straight to what you need.</p>

    <div class="sf-help__callout">
        <div class="sf-help__calloutTitle">Tip</div>
        <div>If a driver says “I can’t start a trip”, it’s almost always a required field in Company Settings.</div>
    </div>
</section>

<section id="admin-ref-company-settings" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Company settings & rules</h3>
    <details class="sf-help__details">
        <summary>What do company settings control?</summary>
        <div class="text-muted" style="margin-top: 8px;">Company settings decide what drivers see, what is required, and what is locked.</div>
        <ul class="mb-0" style="margin-top: 10px;">
            <li><strong>Required fields:</strong> if required, drivers cannot start/end a trip until it’s filled in.</li>
            <li><strong>Locked fields:</strong> if overrides are off, drivers can’t “fix” readings by guessing.</li>
            <li><strong>Optional features:</strong> safety checks and client/customer capture can be enabled per company.</li>
        </ul>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/settings">Open Company Settings</a>
        </div>
    </details>
</section>

<section id="admin-ref-trip-rules" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Trip rules</h3>
    <details class="sf-help__details">
        <summary>What are “trip rules” in plain English?</summary>
        <div class="text-muted" style="margin-top: 8px;">Trip rules decide what a driver must enter when starting and ending a trip.</div>
        <ul class="mb-0" style="margin-top: 10px;">
            <li><strong>Start reading required:</strong> driver must enter a starting odometer/hours reading.</li>
            <li><strong>Overrides allowed:</strong> if off, drivers can’t change the suggested reading.</li>
            <li><strong>Private trips:</strong> lets drivers mark trips as private (if your company allows it).</li>
            <li><strong>Manual times:</strong> lets you require start/end times to be entered (useful for backdated trips).</li>
        </ul>
    </details>
</section>

<section id="admin-ref-client-customer" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Client / customer capture</h3>
    <details class="sf-help__details">
        <summary>How do customer fields work?</summary>
        <div class="text-muted" style="margin-top: 8px;">Customer capture can show on business trips depending on your setup.</div>
        <ul class="mb-0" style="margin-top: 10px;">
            <li><strong>Client presence question:</strong> you can ask “Was a customer present?” and optionally make it required.</li>
            <li><strong>Customer name:</strong> drivers may select a customer or type one (depending on your rules).</li>
            <li><strong>Label:</strong> you can rename the wording drivers see (Customer/Client/Passenger).</li>
        </ul>
        <div class="sf-help__callout sf-help__callout--important">
            <div class="sf-help__calloutTitle">Important</div>
            <div>If you make it required, drivers will be blocked until they answer it.</div>
        </div>
    </details>
</section>

<section id="admin-ref-safety" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Safety checks</h3>
    <details class="sf-help__details">
        <summary>When should we use safety checks?</summary>
        <div class="text-muted" style="margin-top: 8px;">Use safety checks when you want a quick, consistent pre-start check.</div>
        <ul class="mb-0" style="margin-top: 10px;">
            <li>Keep the checklist short.</li>
            <li>Only make it required if your process needs it.</li>
        </ul>
        <div class="sf-help__actions">
            <a class="btn btn-secondary btn-sm" href="/app/sharpfleet/admin/safety-checks">Open Safety Checks</a>
        </div>
    </details>
</section>

<section id="admin-ref-vehicles" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Vehicles & maintenance</h3>
    <details class="sf-help__details">
        <summary>How do we manage vehicles day-to-day?</summary>
        <div class="text-muted" style="margin-top: 8px;">Vehicles can be active, out of service (temporary), or archived (no longer used).</div>
        <ul class="mb-0" style="margin-top: 10px;">
            <li><strong>Out of service:</strong> blocks bookings and trip starts. Use for service/repair/inspection.</li>
            <li><strong>Archive:</strong> hides the vehicle from day-to-day use, but keeps history.</li>
            <li><strong>Reminders:</strong> rego and service reminders depend on your Company Settings.</li>
        </ul>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/vehicles">Open Vehicles</a>
        </div>
    </details>
</section>

<section id="admin-ref-reports" data-sf-help-section class="sf-help__section">
    <h3 class="sf-help__sectionTitle">Reports & exports</h3>
    <details class="sf-help__details">
        <summary>How do trip reports work?</summary>
        <div class="text-muted" style="margin-top: 8px;">Reports are designed for exporting and logbook workflows. Some filters may be locked by your company settings.</div>
        <ol class="mb-0" style="margin-top: 10px;">
            <li>Open Trip Reports.</li>
            <li>Check the <strong>Applied settings</strong> area at the top.</li>
            <li>Adjust filters if available, then refresh results.</li>
            <li>Use Export CSV to download the same results you’re viewing.</li>
        </ol>
        <div class="sf-help__actions">
            <a class="btn btn-primary btn-sm" href="/app/sharpfleet/admin/reports/trips">Open Trip Reports</a>
        </div>
    </details>
</section>

<section id="admin-faq" data-sf-help-section class="sf-help__section">
    <h2 class="sf-help__sectionTitle">Common questions</h2>

    <details class="sf-help__details">
        <summary>Why are some fields missing on the driver screen?</summary>
        <div class="text-muted" style="margin-top: 8px;">Because your company settings control what drivers see.</div>
        <div style="margin-top: 10px;">Check <strong>Company Settings</strong> for trip rules, client presence, and safety checks.</div>
    </details>

    <details class="sf-help__details">
        <summary>Why can’t a driver change a reading?</summary>
        <div class="text-muted" style="margin-top: 8px;">Your company may have disabled overrides.</div>
        <div style="margin-top: 10px;">If you want drivers to correct mistakes, enable overrides in Company Settings.</div>
    </details>

    <details class="sf-help__details">
        <summary>Why are filters disabled on reports?</summary>
        <div class="text-muted" style="margin-top: 8px;">Some companies lock report filters for consistency.</div>
        <div style="margin-top: 10px;">The <strong>Applied settings</strong> box explains what is locked.</div>
    </details>

    <details class="sf-help__details">
        <summary>Do we need GPS tracking for trips?</summary>
        <div class="text-muted" style="margin-top: 8px;">No — SharpFleet does not assume GPS.</div>
        <div style="margin-top: 10px;">Trips are based on what the driver enters and what your company rules require.</div>
    </details>
</section>

@endsection
