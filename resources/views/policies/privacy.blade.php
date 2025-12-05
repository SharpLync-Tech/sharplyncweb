@extends('policies.layout')

@section('policy_title', 'Privacy Policy')

@section('policy_back')
<div class="policy-back-wrapper">
    <a href="/policies/hub" class="policy-back-btn">Back to Policies</a>
</div>
@endsection

@section('policy_version', 'v1.3')
@section('policy_updated', '19 November 2025')

@section('policy_content')

<div class="policy-section">
    <h2>1. Our Commitment to Australian Privacy Law</h2>

    <h3>1.1 Compliance with Australian Privacy Principles (APPs)</h3>
    <p>
        SharpLync Pty Ltd is committed to complying with the <strong>Privacy Act 1988 (Cth)</strong>, including the 
        <strong>Australian Privacy Principles (APPs)</strong>. These principles govern the way we handle, use, and 
        manage your personal information.
    </p>

    <p>This policy reflects our commitment to:</p>

    <ul>
        <li><strong>Open and Transparent Management (APP 1):</strong> Clearly outlining our personal information management practices.</li>
        <li><strong>Collection of Solicited Information (APP 3):</strong> Only collecting personal information that is reasonably necessary for providing our IT services and managing our business functions.</li>
        <li><strong>Notification of Collection (APP 5):</strong> Ensuring you are aware of the details of our data collection practices at or before the time we collect your information.</li>
        <li><strong>Security (APP 11):</strong> Taking reasonable steps to protect the personal information we hold from misuse, interference, loss, unauthorised access, modification, or disclosure (as detailed in Section 4).</li>
        <li><strong>Access and Correction (APP 12 & 13):</strong> Providing you with the right to access and correct your personal information (as detailed in Section 7).</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>2. The Information We Collect</h2>

    <p>
        We collect personal information that is reasonably necessary for us to provide our services and manage our 
        business operations. This information is typically gathered during customer registration on our website or 
        when you engage with us for services.
    </p>

    <p>The types of personal information we collect may include:</p>

    <ul>
        <li><strong>Identity and Contact Data:</strong> Your full name, company name, business address, telephone number, and email address.</li>
        <li><strong>Service Data:</strong> Details regarding the IT services, hardware, or software you require or have purchased.</li>
        <li><strong>Financial Data:</strong> While we use third-party payment processors for billing, we collect information necessary to issue invoices.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>3. How We Use Your Information</h2>

    <p>We use the personal information we collect for the following primary purposes:</p>

    <ul>
        <li><strong>Service Provision:</strong> To deliver IT support, hardware, software, and consultancy services, and to manage your customer account.</li>
        <li><strong>Communication:</strong> To contact you regarding service requests, support issues, and account status.</li>
        <li><strong>Billing and Administration:</strong> To process payments, issue invoices, and manage our business records.</li>
        <li><strong>Marketing and Promotions:</strong> To send you updates, promotional materials, or other information about our services, subject to your clear consent and the right to opt-out.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>4. Storage and Security</h2>

    <p>
        We are committed to ensuring the security of your personal information, particularly when handling support requests.
    </p>

    <p><strong>Data Storage Location:</strong> Your customer details are stored securely in a MySQL database hosted within Microsoft Azure.</p>

    <p><strong>Security Measures:</strong> We implement a range of technical and organizational security measures, including access controls, encryption, and regular security reviews, to protect your personal data from unauthorized access, loss, misuse, or disclosure.</p>

    <h3>4.1 Verification and Reverse Authentication for Support</h3>

    <p>
        To protect your account and identity from social engineering and phishing, we employ a stringent, customer-first 
        security protocol when you engage with our support team.
    </p>

    <p><strong>Reverse Authentication PIN (Mandatory Staff Verification):</strong>  
        When you call us or we call you, you must first ask the SharpLync support staff member for the unique security PIN 
        (Personal Identification Number) that is known only to you and registered in our secure system. We will not proceed 
        with any support request or discuss account details until you have successfully verified our identity using this PIN. 
        This process ensures you are speaking only with authorized SharpLync personnel.
    </p>

    <p><strong>Customer Verification:</strong>  
        Once you have verified our identity using the PIN, our support staff will then proceed to verify your identity by asking 
        you for specific pieces of information stored on your account (such as company name or email address) before 
        accessing any private data or making changes.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>5. Disclosure to Third Parties</h2>

    <p>
        We will only disclose your personal information to third parties when necessary to perform essential business functions 
        or when required by law.
    </p>

    <h3>Billing Providers</h3>
    <p>
        We use third-party financial platforms to manage invoicing and payment processing. Specifically, we use 
        <strong>Xero</strong> for accounting and invoicing and <strong>Stripe</strong> for payment processing. These providers only receive 
        the necessary data (such as company name and contact details) to facilitate the billing process and operate 
        under their own privacy policies. We do not store your full payment card details ourselves.
    </p>

    <h3>Legal Requirements</h3>
    <p>
        We may disclose your information if we are legally required to do so by a governmental or law enforcement agency.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>6. Marketing and Opt-Out</h2>

    <p>We respect your privacy regarding marketing communications:</p>

    <ul>
        <li><strong>Spam Law Adherence:</strong> SharpLync Pty Ltd strictly adheres to applicable spam laws and regulations (e.g., the Australian Spam Act 2003). We will only send you electronic marketing messages if you have provided your express or inferred consent.</li>
        <li><strong>Opt-Out Options:</strong> Every marketing email we send will contain an easily accessible and visible unsubscribe link. You can withdraw your consent to receive marketing communications at any time by clicking this link or by contacting us directly.</li>
    </ul>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>7. Accessing and Correcting Your Information</h2>

    <p>
        You have the right to request access to the personal information we hold about you and to request that we correct 
        any inaccuracies. If you wish to access or correct your details, please contact us using the information provided 
        in Section 9 below.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>8. Changes to This Policy</h2>

    <p>
        SharpLync Pty Ltd may update this Privacy Policy from time to time to reflect changes in our practices or legal 
        obligations. We will notify you of any material changes by posting the updated policy on our website.
    </p>
</div>

<div class="policy-divider"></div>

<div class="policy-section">
    <h2>9. Contact Information</h2>

    <p>If you have any questions about this Privacy Policy or our practices, or if you wish to make a complaint, please contact our Privacy Officer:</p>

    <p><strong>SharpLync Pty Ltd<br>
    Attention: Privacy Officer</strong></p>

    <ul>
        <li>Email: privacy@sharplync.com.au</li>
        <li>Phone: 0404 442 066</li>
        <li>Address: PO Box 1081, Stanthorpe QLD 4380</li>
    </ul>
</div>

@endsection
