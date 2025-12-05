@extends('policies.layout')

@section('policy_title', 'Security Policy')

@section('policy_back')
<div class="policy-back-wrapper">
    <a href="/policies/hub" class="policy-back-btn">Back to Policies</a>
</div>
@endsection

@section('policy_version', 'v1.0')
@section('policy_updated', '5 December 2025')

@section('policy_content')

<div class="policy-section">
    <h2>1. Purpose of This Policy</h2>
    <p>
        This Security Policy outlines the controls, technologies, and procedures SharpLync uses to protect our 
        internal systems, customer information, cloud environment, and remote support operations. Our goal is to 
        provide transparent confidence in how we secure the data entrusted to us.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>2. Security Principles We Operate By</h2>

    <ul>
        <li><strong>Zero Trust:</strong> No implicit trust. All access is verified.</li>
        <li><strong>Least Privilege:</strong> Staff access only what is required for their role.</li>
        <li><strong>Encryption Everywhere:</strong> Data encrypted in transit and at rest.</li>
        <li><strong>Continuous Monitoring:</strong> Threats are continuously assessed and acted upon.</li>
        <li><strong>Audit Logging:</strong> Key systems and support activities are logged and reviewable.</li>
        <li><strong>Security by Design:</strong> We build security into every system from day one.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>3. Trend Micro Vision One Security Platform</h2>

    <p>
        As an official Trend Micro Vision One MSP Partner, SharpLync uses this enterprise-grade platform to protect 
        both internal systems and customer endpoints under management plans.
    </p>

    <p>The Trend Micro Vision One platform provides:</p>

    <ul>
        <li>Zero-day exploit prevention</li>
        <li>AI-driven behavioural detection</li>
        <li>Endpoint Detection & Response (EDR)</li>
        <li>Ransomware rollback and isolation</li>
        <li>Email threat correlation and phishing detection</li>
        <li>Identity-based threat analysis</li>
        <li>XDR telemetry across endpoints, email, and identities</li>
    </ul>

    <p>
        Through this platform, SharpLync receives a unified view of threats, real-time alerts, and automated actions 
        to contain and neutralise risks quickly.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>4. Microsoft Azure Security</h2>

    <p>
        All SharpLync websites, internal tools, APIs, authentication systems, and customer portals run inside the 
        Microsoft Azure cloud platform. Azure provides enterprise-level protection that includes:
    </p>

    <h3>4.1 Data Encryption</h3>
    <ul>
        <li><strong>At rest:</strong> All customer and SharpLync data is encrypted using AES-256.</li>
        <li><strong>In transit:</strong> All communication uses TLS 1.2 or higher.</li>
    </ul>

    <h3>4.2 Azure Web App Security</h3>
    <ul>
        <li>Sandboxed application environments</li>
        <li>Automatic security patching</li>
        <li>Web firewall and DDoS protection</li>
        <li>Threat scanning with Microsoft Defender for Cloud</li>
    </ul>

    <h3>4.3 Azure Key Vault</h3>
    <p>
        SharpLync uses Azure Key Vault to store and manage all sensitive service credentials, database passwords, 
        encryption keys, and third-party tokens. Nothing sensitive is stored in code repositories or application config files.
    </p>

    <h3>4.4 Network Controls</h3>
    <ul>
        <li>IP-restricted access to administrative services</li>
        <li>Azure DDoS protection</li>
        <li>Private networking for databases</li>
        <li>Firewall rules restricting inbound traffic to approved paths only</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>5. Database & Financial Data Security</h2>

    <h3>5.1 Azure MySQL Flexible Server</h3>
    <p>
        Customer data, portal information, and SharpLync system data are stored in Azure MySQL Flexible Server with 
        enterprise security controls enabled. Databases are encrypted, access-controlled, and protected using private 
        networking and firewalls.
    </p>

    <h3>5.2 Encryption and Access Control</h3>
    <ul>
        <li>Encrypted at rest (AES-256)</li>
        <li>Encrypted in transit (TLS)</li>
        <li>Access limited to SharpLync applications and authorised technicians</li>
        <li>Separate admin and application users with least privilege access</li>
    </ul>

    <h3>5.3 Backup and Restore</h3>
    <ul>
        <li>Automatic daily backups with point-in-time restore</li>
        <li>Multi-region redundancy</li>
        <li>No manual intervention required for backup management</li>
    </ul>

    <h3>5.4 Payment Data (We Do NOT Store Customer Card Information)</h3>
    <p>
        SharpLync does <strong>not</strong> store or process payment card data under any circumstance.
        All payments are securely handled by:
    </p>

    <ul>
        <li><strong>Xero:</strong> invoicing and account billing</li>
        <li><strong>Stripe:</strong> secure card transactions</li>
        <li><strong>PayPal:</strong> optional customer checkout</li>
    </ul>

    <p>
        These providers are PCI-DSS compliant and store card data using their own protected systems.  
        SharpLync systems never see, store, or transmit credit card numbers, CVV codes, or bank login credentials.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>6. Internal SharpLync Security Controls</h2>

    <h3>6.1 Password & Credential Management</h3>
    <p>
        SharpLync uses <strong>Bitwarden</strong> for secure credential storage. All internal passwords, service 
        tokens, certificates, and administrative credentials are stored in encrypted Bitwarden vaults protected by:
    </p>

    <ul>
        <li>Zero-knowledge encryption</li>
        <li>Multi-factor authentication</li>
        <li>Role-based access controls</li>
        <li>Breach monitoring and credential health auditing</li>
    </ul>

    <p><strong>Passwords are never stored in plain text, emails, or local documents.</strong></p>

    <h3>6.2 Device Security</h3>
    <ul>
        <li>Trend Micro endpoint protection</li>
        <li>Full disk encryption</li>
        <li>Secure boot enabled</li>
        <li>Remote wipe capabilities</li>
        <li>Conditional access based on device compliance</li>
    </ul>

    <h3>6.3 Identity Protection</h3>
    <ul>
        <li>Azure Active Directory authentication</li>
        <li>Mandatory MFA for all SharpLync staff</li>
        <li>Conditional access policies to block high-risk sign-ins</li>
        <li>No shared accounts</li>
    </ul>

    <h3>6.4 Staff Privilege Controls</h3>
    <ul>
        <li>Least-privilege access for all staff</li>
        <li>Regular access reviews</li>
        <li>Revocation of access on role changes or offboarding</li>
        <li>Activity logging for auditing purposes</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>7. Customer Support Security</h2>

    <h3>7.1 Reverse Authentication PIN</h3>
    <p>
        SharpLync uses a mandatory Reverse Authentication PIN system. Before discussing your account or providing 
        assistance, you must ask the technician to provide your confidential PIN stored in your customer profile.  
        If the PIN does not match, the call must be terminated immediately.
    </p>

    <h3>7.2 Session PIN for Remote Support</h3>
    <p>
        Remote sessions can only begin using a <strong>unique, time-sensitive Session PIN</strong>. Once the session ends, 
        the PIN expires permanently and cannot be reused.
    </p>

    <h3>7.3 Session Recording</h3>
    <p>
        All remote sessions are logged and recorded for quality control, security auditing, and dispute verification.  
        No permanent remote access tools are installed unless you provide explicit written approval.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>8. Data Retention and Lifecycle Management</h2>
    <p>
        SharpLync retains customer data only for as long as necessary to deliver services or comply with legal 
        obligations. When data is no longer required, it is securely deleted from production systems, and backups 
        naturally expire through Azure's automated retention policies.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>9. Incident Response & Reporting</h2>
    <p>
        SharpLync operates an internal incident response framework backed by Trend Micro Vision One’s threat 
        correlation engine. Our procedures include:
    </p>

    <ul>
        <li>24/7 threat monitoring</li>
        <li>Automated detection and containment</li>
        <li>Rapid isolation of compromised devices</li>
        <li>Immediate customer notification where relevant</li>
        <li>Documented remediation and after-action reporting</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>10. Customer Responsibilities</h2>

    <p>To maintain a secure environment, customers must:</p>

    <ul>
        <li>Keep passwords secure and private</li>
        <li>Maintain regular backups (unless using SharpLync Managed Backup)</li>
        <li>Inform SharpLync of staff changes or departures</li>
        <li>Notify SharpLync of suspected security incidents promptly</li>
        <li>Keep systems and software updated</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>11. Contact for Security Concerns</h2>

    <p>
        If you have any questions or concerns regarding this Security Policy, please contact:
    </p>

    <p><strong>SharpLync Security Team</strong></p>
    <ul>
        <li>Email: security@sharplync.com.au</li>
        <li>Phone: 0492 014 463</li>
        <li>Address: PO Box 1081, Stanthorpe QLD 4380</li>
    </ul>
</div>

@endsection
