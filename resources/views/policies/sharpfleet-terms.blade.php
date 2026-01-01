@extends('policies.layout')

@section('policy_title', 'SharpFleet – Terms & Conditions')

@section('policy_back')
<div class="policy-back-wrapper">
    <a href="/policies/hub" class="policy-back-btn">Back to Policies</a>
</div>
@endsection

@section('policy_version', 'v1.0')
@section('policy_updated', '1 January 2026')

@section('policy_content')

<div class="policy-section">
    <h2>Introduction</h2>
    <p>
        These Terms &amp; Conditions (“Terms”) govern your access to and use of SharpFleet, a software-as-a-service (SaaS)
        platform operated by SharpLync Pty Ltd (“SharpLync”, “we”, “us”, “our”).
    </p>
    <p>
        By accessing or using SharpFleet, you agree to be bound by these Terms.
        If you do not agree, you must not use the platform.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>1. The SharpFleet Service</h2>

    <p>
        SharpFleet is a cloud-based fleet and driver logbook platform designed to assist businesses with record-keeping and reporting.
    </p>

    <p><strong>SharpFleet:</strong></p>
    <ul>
        <li>Records trip, vehicle, and usage information</li>
        <li>Provides summaries and reports based on user-entered data</li>
        <li>Does not provide real-time GPS tracking</li>
        <li>Does not monitor, control, or enforce driver behaviour</li>
        <li>Does not guarantee legal, tax, or regulatory compliance</li>
    </ul>

    <p><strong>SharpFleet is a record-keeping tool only.</strong></p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>2. Eligibility &amp; Account Responsibility</h2>

    <p><strong>You must:</strong></p>
    <ul>
        <li>Be legally capable of entering into a binding agreement</li>
        <li>Ensure all users (admins, managers, drivers) are authorised</li>
        <li>Keep account credentials secure</li>
    </ul>

    <p>
        You are solely responsible for all activity performed under your account, including actions taken by employees, contractors, or drivers.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>3. Legal Compliance &amp; Driver Responsibility</h2>

    <p><strong>You acknowledge and agree that:</strong></p>
    <ul>
        <li>You are responsible for ensuring SharpFleet is used in compliance with all applicable local, state, and federal laws</li>
        <li>This includes road rules, workplace safety obligations, and mobile phone usage laws</li>
        <li>SharpFleet does not monitor or enforce lawful use</li>
    </ul>

    <h3>Mobile Phone Use While Driving</h3>
    <p>
        The business owner and each driver are solely responsible for complying with local laws relating to mobile phone use while operating a vehicle.
    </p>

    <p>
        (SharpLync promotes safe driving practices and does not endorse the use of the SharpFleet application while driving.
        Best practice is to start trip logs before driving and to safely park the vehicle before stopping or ending a trip log.)
    </p>

    <p>
        SharpLync accepts no responsibility for fines, penalties, incidents, or legal consequences arising from misuse of the platform.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>4. Acceptable Use</h2>

    <p><strong>You must not:</strong></p>
    <ul>
        <li>Use SharpFleet for unlawful or unauthorised purposes</li>
        <li>Submit false, misleading, or fraudulent data</li>
        <li>Attempt to access other customers’ data</li>
        <li>Interfere with or disrupt the platform or its security</li>
    </ul>

    <p>
        We reserve the right to suspend or terminate access where misuse, abuse, or risk is identified.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>5. Data Ownership, Storage &amp; Location</h2>

    <ul>
        <li>You retain ownership of all data entered into SharpFleet</li>
        <li>You are responsible for the accuracy and completeness of that data</li>
        <li>Reports are generated solely from information provided by users</li>
    </ul>

    <p><strong>All SharpFleet data is:</strong></p>
    <ul>
        <li>Stored and processed within Australia</li>
        <li>Hosted using reputable Australian-based infrastructure providers</li>
    </ul>

    <h3>No Artificial Intelligence Usage</h3>
    <p>
        SharpFleet does not use artificial intelligence or machine learning systems to analyse, interpret, or make decisions about your data.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>6. Privacy &amp; Security</h2>

    <p>
        We take reasonable technical and organisational steps to protect your data.
        However, you acknowledge that no online system can be guaranteed to be completely secure.
    </p>

    <p>
        Further details are available in our <a href="/policies/privacy">Privacy Policy</a>.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>7. Billing, Payments &amp; Subscriptions</h2>

    <p>
        SharpFleet is offered on a subscription basis.
    </p>

    <ul>
        <li>All payments are processed securely via Stripe</li>
        <li>SharpLync does not store or process credit card or payment details</li>
        <li>Payment information is handled directly by Stripe in accordance with their security standards</li>
        <li>Subscription fees are charged in advance for each billing period</li>
        <li>You may cancel your subscription at any time</li>
        <li>If you cancel during an active billing period:
            <ul>
                <li>Your access to SharpFleet will continue until the end of the paid billing period</li>
                <li>Your subscription will not renew for the next period</li>
            </ul>
        </li>
        <li>No pro-rata refunds, partial refunds, or credits are provided</li>
    </ul>

    <p>
        By subscribing, you acknowledge that payments are non-refundable once a billing period has commenced.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>8. Availability &amp; Service Changes</h2>

    <p>
        We aim to provide reliable access to SharpFleet, however:
    </p>

    <ul>
        <li>Availability is not guaranteed</li>
        <li>Maintenance, updates, or outages may occur</li>
        <li>Features may be modified, improved, or discontinued over time</li>
    </ul>

    <p>
        SharpLync is not liable for downtime or service interruptions beyond our reasonable control.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>9. Limitation of Liability</h2>

    <p><strong>To the maximum extent permitted by law:</strong></p>
    <ul>
        <li>SharpLync is not liable for indirect, incidental, or consequential losses</li>
        <li>We are not responsible for fines, penalties, or legal breaches arising from driver conduct or data entry</li>
        <li>Our total liability is limited to the amount paid for SharpFleet in the previous 12 months</li>
    </ul>

    <p>
        Use of SharpFleet is entirely at your own risk.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>10. Indemnity</h2>

    <p>
        You agree to indemnify and hold harmless SharpLync Pty Ltd from any claims, losses, damages, or liabilities arising from:
    </p>

    <ul>
        <li>Use of SharpFleet</li>
        <li>Driver actions or inactions</li>
        <li>Breach of these Terms or applicable laws</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>11. Intellectual Property</h2>

    <p>
        All SharpFleet software, branding, and content remain the intellectual property of SharpLync Pty Ltd.
    </p>

    <p>
        You are granted a non-exclusive, non-transferable licence to use the platform.
        No copying, resale, modification, or reverse engineering is permitted.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>12. Suspension &amp; Termination</h2>

    <p>
        SharpLync may suspend or terminate access if:
    </p>

    <ul>
        <li>These Terms are breached</li>
        <li>Payment obligations are not met</li>
        <li>There is a security, legal, or operational risk</li>
    </ul>

    <p>
        Termination does not affect obligations incurred prior to termination.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>13. Changes to These Terms</h2>

    <p>
        We may update these Terms from time to time.
        Continued use of SharpFleet after changes take effect constitutes acceptance of the updated Terms.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>14. Governing Law</h2>

    <p>
        These Terms are governed by the laws of Queensland, Australia.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>15. Contact</h2>

    <p>
        For questions regarding these Terms, please contact us via the SharpFleet or SharpLync website.
    </p>
</div>

@endsection
