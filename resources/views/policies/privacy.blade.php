@extends('policies.layout')

@section('policy_title', 'Privacy Policy')
@section('policy_back')
<div class="policy-back-wrapper">
    <a href="/policies/hub" class="policy-back-btn">Back to Policies</a>
</div>
@endsection
@section('policy_version', 'v1.3')
@section('policy_updated', '5 December 2025')

@section('policy_content')

<div class="policy-section">
    <h2>Our Commitment to Australian Privacy Law</h2>
    <p>SharpLync Pty Ltd complies with the Privacy Act 1988 (Cth) and the Australian
    Privacy Principles (APPs), including obligations around data collection, storage,
    notification, access, and correction.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>The Information We Collect</h2>
    <p>We collect only information reasonably necessary to provide IT services and operate
    our business. This may include identity data, contact details, service information, and
    invoice-related data.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>How We Use Your Information</h2>
    <p>Your information is used for service delivery, communication, support, billing,
    administration, and — only with consent — marketing.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Storage and Security</h2>
    <p>Customer data is securely stored in Microsoft Azure using encryption, access control,
    and other safeguards.</p>

    <p><strong>Support Verification:</strong> SharpLync uses a Reverse Authentication PIN that YOU
    give to staff to confirm they are legitimate before discussing your account. Only after
    staff verify themselves do they verify you.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Disclosure to Third Parties</h2>
    <p>We disclose information only when necessary — such as billing providers (Xero,
    Stripe) — or when legally required. We do not store full payment card details.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Marketing and Opt-Out</h2>
    <p>Marketing communications comply with the Australian Spam Act 2003. You may opt
    out at any time using unsubscribe links or by contacting us.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Accessing and Correcting Your Information</h2>
    <p>You may request access or corrections to your personal information at any time by
    contacting us.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Changes to This Policy</h2>
    <p>SharpLync may update this policy as required. Material updates will be posted on our
    website.</p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Contact Information</h2>
    <p>You may contact our Privacy Officer at:</p>
    <ul>
        <li>Email: privacy@sharplync.com.au</li>
        <li>Phone: 0492 014 463</li>
        <li>Mail: PO Box 1081, Stanthorpe QLD 4380</li>
    </ul>
</div>


@endsection
