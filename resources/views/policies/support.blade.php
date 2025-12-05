@extends('policies.layout')

@section('policy_title', 'Privacy Policy')
@section('policy_version', 'v1.3')
@section('policy_updated', '5 December 2025')

@section('policy_content')

<div class="policy-section">
    <h2>Our Commitment to Your Security</h2>
    <p>
        SharpLync places the highest priority on your security during any remote support session.
        While remote access tools are essential for efficient IT support, we also understand they
        are frequently abused by scammers. Because of this, we implement strict, non-negotiable
        procedures designed to guarantee your safety and ensure you only ever connect with a
        verified SharpLync technician.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Two-Way Identity Verification Protocol</h2>
    <p>
        Before any remote session can begin, we follow a mandatory multi-step verification
        process designed to confirm our identity to you before you confirm your identity to us.
        This prevents impersonation and eliminates the risk of fraudulent access.
    </p>

    <h3 style="margin-top:1rem;">1. You Verify Us First (Personal Verification PIN)</h3>
    <ul>
        <li><strong>The Challenge:</strong> You must ask the technician to provide your confidential Personal Verification PIN.</li>
        <li><strong>The Proof:</strong> The technician will read out the exact PIN stored in your secure account profile.</li>
        <li><strong>Your Confirmation:</strong> You must confirm the PIN matches your own. If it does not match, the call must be terminated immediately.</li>
    </ul>

    <h3 style="margin-top:1rem;">2. Forward Identification (Session PIN)</h3>
    <p>
        Once our identity has been confirmed, the technician will provide a unique, time-sensitive
        <strong>Session PIN</strong> required to establish the remote connection.
    </p>

    <p><strong>No remote session will begin until:</strong></p>
    <ul>
        <li>Your Personal Verification PIN is verified</li>
        <li>You receive and enter the valid Session PIN</li>
    </ul>

    <p>
        This process guarantees that you are always in full control and that only legitimate SharpLync
        staff can initiate a session.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Strict Financial Security Rules</h2>
    <p>
        SharpLync enforces strict financial safety procedures during all remote support sessions.
        These rules protect you from fraud and ensure no technician can request sensitive financial
        information or inappropriate payment methods.
    </p>

    <ul>
        <li><strong>No banking websites</strong> may be open during a session.</li>
        <li><strong>No technician will ever request passwords</strong> or financial credentials.</li>
        <li><strong>Only official Xero invoices</strong> are used for payment processing.</li>
        <li><strong>Approved payment methods:</strong> Credit Card, PayPal, Bank Transfer.</li>
        <li><strong>We will never request payment</strong> via gift cards, cryptocurrency, or cash withdrawals.</li>
        <li><strong>No technician will ever instruct you</strong> to visit your bank or phone your bank.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>If You Suspect a Scam</h2>
    <p>
        If anyone claiming to be from SharpLync violates any part of this policy—especially by
        requesting restricted payment methods, asking for credentials, or attempting to rush you—
        you must treat the situation as suspicious.
    </p>

    <ul>
        <li>❌ <strong>Hang up immediately</strong></li>
        <li>❌ <strong>Do not allow any remote access</strong></li>
        <li>✅ <strong>Call our official SharpLync support number</strong> to verify the incident</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Tools, Transparency & Your Control</h2>
    <h3 style="margin-top:1rem;">A. Tools We Use</h3>
    <p>
        SharpLync uses secure, professional-grade remote support tools such as TeamViewer.
        These are encrypted and safe, but scammers often misuse their names—which is why
        our verification protocol (Section 2) must always occur first.
    </p>
    <p>
        Access is always <strong>one-time only</strong> unless you provide explicit written consent for
        unattended access.
    </p>

    <h3 style="margin-top:1rem;">B. Your Control During the Session</h3>
    <ul>
        <li>You must remain at your computer and monitor the session.</li>
        <li>Technicians will verbally explain major actions before performing them.</li>
        <li>You may terminate the session at any moment by closing the window.</li>
        <li>If something feels wrong, disconnect immediately and contact us.</li>
    </ul>

    <h3 style="margin-top:1rem;">C. Session Logging & Privacy</h3>
    <ul>
        <li>All remote support sessions are <strong>logged and video recorded</strong> for quality, security, and dispute resolution.</li>
        <li>Technicians are strictly prohibited from accessing personal files unless you request it for troubleshooting.</li>
        <li>All data handling follows the SharpLync Privacy Policy.</li>
    </ul>

    <h3 style="margin-top:1rem;">D. Post-Session Protocol</h3>
    <ul>
        <li>We will confirm that the session is fully terminated.</li>
        <li>The temporary Session PIN will expire immediately.</li>
        <li>No technician can reconnect without a new PIN from you.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Summary</h2>
    <p>
        SharpLync’s Secure Remote Support Policy ensures every remote session is safe,
        authenticated, transparent, and fully controlled by you.  
        We appreciate your cooperation in following this process—it protects both you  
        and SharpLync against impersonation and fraud.
    </p>
</div>
@endsection