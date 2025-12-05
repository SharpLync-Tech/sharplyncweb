@section('policy_content')

<div class="policy-section">
    <h2>SharpLync Secure Remote Support Policy</h2>
    <p>
        At SharpLync, your security is our highest priority. We use industry-leading, secure remote access 
        tools to provide efficient support. We understand that these same tools can be exploited by malicious 
        actors (scammers). This document outlines our strict protocol to ensure your safety and give you 
        absolute confidence that you are connecting only with an authenticated SharpLync representative.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>1. Our Commitment to Your Security</h2>
    <p>
        We take customer security and the integrity of our support process extremely seriously. We have implemented 
        several clear, non-negotiable procedures that we will follow on every single remote support call. If any person 
        claiming to be from SharpLync deviates from this protocol, you must immediately terminate the call.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>2. The Two-Way Identity Verification Protocol</h2>
    <p>
        To ensure absolute security, we use a two-step verification process where you, the customer, confirm our identity first. 
        This requires your Personal Verification PIN, which is confidential and known only to you and SharpLync's secure system.
    </p>

    <p>Before we proceed with any remote connection or sensitive discussion, the following mandatory steps must occur:</p>

    <h3 style="margin-top:1rem;">Customer Requests PIN (The Challenge)</h3>
    <p>You, the customer, must ask the SharpLync technician to provide your confidential Personal Verification PIN.</p>

    <h3 style="margin-top:1rem;">SharpLync Provides Customer PIN (The Proof)</h3>
    <p>The technician will read back this exact PIN from your secure account record.</p>

    <h3 style="margin-top:1rem;">Customer Verifies Identity (The Veto)</h3>
    <p>You must confirm that the PIN provided by the technician matches your Personal Verification PIN. If the PIN does not match, you must immediately hang up.</p>

    <h3 style="margin-top:1rem;">Forward Identification (Session PIN)</h3>
    <p>
        Once our identity is confirmed, the technician will then provide a unique, time-sensitive Session PIN which is required 
        to start the actual remote connection software.
    </p>

    <h3 style="margin-top:1rem;">Connection Only After Verification</h3>
    <p>
        We will only begin the remote connection process once both the Personal Verification PIN (Step 3) and the Session PIN (Step 4) 
        are successfully verified.
    </p>

    <p>
        <strong>You identify us first</strong>, using a secret only you and our secure system know. This guarantees our authenticity.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>3. Strict Financial Security Rules</h2>
    <p>
        SharpLync has an absolute, zero-tolerance policy regarding customer financial security. We insist on the following rules 
        during any remote support session:
    </p>

    <ul>
        <li><span class="sl-icon-bad">✘ No Open Banking Sites:</strong> All banking websites, financial applications, and investment accounts must be closed or minimized before we establish a remote connection.</li>
        <li><strong>✘ No Password Requests:</strong> We will never ask for banking passwords, credit card PINs, or any login credentials.</li>
        <li><strong>✓ Secure Invoicing Only:</strong> All payments are processed via official Xero invoices emailed directly to you.</li>
        <li><strong>✓ Approved Payment Methods:</strong> Credit Card, PayPal, or Bank Transfer via Xero invoice only.</li>
        <li><strong>✘ Never Gift Cards:</strong> We will never request Apple, Amazon, Google Play, or any other gift cards.</li>
        <li><strong>✘ Never Cryptocurrency:</strong> We will never request Bitcoin, Ethereum, or any other crypto payment.</li>
        <li><strong>✘ Never Bank Visits:</strong> We will never ask you to ring your bank, drive to your bank, or withdraw cash.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>4. What To Do If You Suspect a Scam</h2>
    <p>
        If anyone claiming to be from SharpLync asks for any forbidden payment methods (gift cards, crypto, cash withdrawal) or pressures 
        you to provide banking details or open financial websites, you must treat the situation as suspicious.
    </p>

    <ul>
        <li><span class="sl-icon-bad">✘</span> Hang up immediately</li>
        <li><span class="sl-icon-bad">✘</span> Do not allow any remote access</li>
        <li><span class="sl-icon-good">✓</span> Call the official SharpLync support number to verify the incident</li>

    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>5. Building Trust Through Transparency and Control</h2>

    <h3 style="margin-top:1rem;">A. Clear Access Scope and Tools</h3>
    <p>
        We use professional, encrypted remote desktop solutions such as TeamViewer. These tools are secure, but we understand their names 
        are sometimes misused by scammers. Always refer to Section 2 for identity verification first.
    </p>

    <p>
        <strong>One-Time Access:</strong> The Session PIN ensures a one-time, session-only connection. Once the session is closed, the technician 
        cannot reconnect unless a new, unique Session PIN is granted by you. Permanent unattended access is never installed without your explicit 
        written consent.
    </p>

    <h3 style="margin-top:1rem;">B. Customer Control and Monitoring</h3>
    <p><strong>Watch Our Every Move:</strong> Stay at your computer and monitor the remote session at all times. Our technicians will verbally explain major actions.</p>
    <p><strong>Terminate Anytime:</strong> You may immediately disconnect by closing the remote software window. If something feels wrong, disconnect and call us.</p>

    <h3 style="margin-top:1rem;">C. Session Logging and Privacy</h3>
    <p>
        <strong>Session Records:</strong> All sessions are video recorded and logged for quality, security, and dispute resolution.
    </p>
    <p>
        <strong>Data Privacy:</strong> Technicians are prohibited from viewing or copying personal files unless required for troubleshooting. 
        All handling follows the SharpLync Privacy Policy.
    </p>

    <h3 style="margin-top:1rem;">D. Post-Session Protocol</h3>
    <p>
        The technician will confirm the session has ended and that the temporary access link has expired. No technician can reconnect without a new Session PIN.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>Summary</h2>
    <p>
        By adhering to this protocol, we ensure every support session is secure, transparent, and trustworthy. Your safety and confidence 
        remain our highest priority at every step.
    </p>
</div>

@endsection
