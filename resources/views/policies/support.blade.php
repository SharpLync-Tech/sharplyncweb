@extends('policies.layout')

@section('policy_title', 'Secure Remote Support Policy')
@section('policy_version', 'v1.4')
@section('policy_updated', '5 December 2025')

@section('policy_content')

<div class="policy-section">
    <h2>Our Commitment to Your Security</h2>
    <p>
        At SharpLync, your security comes first — every time. Remote support is an essential part of 
        modern IT service, but it is also one of the most commonly exploited methods used by scammers.  
        To protect you, we follow strict verification and safety procedures that make it impossible  
        for an unauthorised person to gain access to your computer under the SharpLync name.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Two-Way Identity Verification Protocol</h2>
    <p>
        No remote support session can begin until both sides have been fully verified.  
        This process ensures you are speaking to a legitimate SharpLync technician — and that we are  
        assisting the correct person.
    </p>

    <h3 style="margin-top:1rem;">1. You Verify Us First (Your Personal Verification PIN)</h3>
    <ul>
        <li><strong>The Challenge:</strong> You must request your confidential Personal Verification PIN from the technician.</li>
        <li><strong>The Proof:</strong> The technician will provide the exact PIN stored in your secure customer profile.</li>
        <li><strong>Your Confirmation:</strong> If the PIN does not match what you have on record, the call must be ended immediately.</li>
    </ul>

    <h3 style="margin-top:1rem;">2. SharpLync Verifies You (Session PIN)</h3>
    <p>
        After we have proven our identity, we will issue a unique, time-sensitive 
        <strong>Session PIN</strong>. This PIN is required to begin the remote support session.
    </p>

    <p><strong>Remote access will only begin once:</strong></p>
    <ul>
        <li>Your Personal Verification PIN is successfully confirmed</li>
        <li>You receive and enter the one-time Session PIN</li>
    </ul>

    <p>
        This process ensures that remote access is always initiated by you — never by us — and  
        only after you confirm the connection is genuine.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Strict Financial Safety Rules</h2>
    <p>
        SharpLync technicians follow strict financial protection standards designed to eliminate the  
        risk of fraud or pressured payments during remote sessions.
    </p>

    <ul>
        <li><strong>No banking websites</strong> may be open during a session.</li>
        <li><strong>No technician will ever request passwords</strong> or financial credentials.</li>
        <li><strong>Only official Xero invoices</strong> are used for billing and payment processing.</li>
        <li><strong>Approved payment methods:</strong> Credit Card, PayPal, Bank Transfer.</li>
        <li><strong>We will never request payment</strong> via gift cards, cryptocurrency, or cash withdrawals.</li>
        <li><strong>No technician will ever instruct you</strong> to visit your bank or call your bank.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>If You Suspect a Scam</h2>
    <p>
        If anyone claiming to be from SharpLync breaks any part of this policy,  
        especially if they request unusual payments, ask for credentials, or rush you,  
        treat the situation as suspicious immediately.
    </p>

    <ul class="sl-warning-list">
    <li><span class="sl-icon-bad">✘</span> Hang up immediately</li>
    <li><span class="sl-icon-bad">✘</span> Do not allow any remote access</li>
    <li><span class="sl-icon-good">✓</span> Call the official SharpLync support number to verify the incident</li>
</ul>



</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Tools, Transparency & Your Control</h2>

    <h3 style="margin-top:1rem;">A. Tools We Use</h3>
    <p>
        SharpLync uses secure, industry-trusted remote support platforms such as TeamViewer.  
        These tools are encrypted and safe, but since scammers often misuse their names,  
        our verification protocol (Section 2) must always occur first.
    </p>

    <p>
        Remote access is always <strong>one-time only</strong> unless you provide written approval  
        for unattended access.
    </p>

    <h3 style="margin-top:1rem;">B. Your Control During the Session</h3>
    <ul>
        <li>You must remain at your computer and actively monitor the session.</li>
        <li>Technicians will clearly explain major actions before performing them.</li>
        <li>You may terminate the session at any time by closing the window.</li>
        <li>If something feels wrong, disconnect immediately and contact us.</li>
    </ul>

    <h3 style="margin-top:1rem;">C. Session Logging & Privacy</h3>
    <ul>
        <li>All remote sessions are <strong>logged and video recorded</strong> for security, quality and dispute resolution.</li>
        <li>Technicians will not access personal files unless you request it for troubleshooting.</li>
        <li>All data handling follows the SharpLync Privacy Policy.</li>
    </ul>

    <h3 style="margin-top:1rem;">D. Post-Session Protocol</h3>
    <ul>
        <li>We confirm that the session has fully ended on both sides.</li>
        <li>The Session PIN expires immediately after use.</li>
        <li>No technician can reconnect without a brand-new PIN supplied by you.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Summary</h2>
    <p>
        SharpLync’s Secure Remote Support Policy ensures every remote session is safe, authenticated  
        and fully controlled by you. By following these steps, both you and SharpLync remain protected  
        against impersonation, fraud and unauthorised access.
    </p>
</div>

@endsection
