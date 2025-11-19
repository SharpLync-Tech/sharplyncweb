@extends('layouts.contact-base')

@section('title', 'SharpLync | Contact Us')

@section('content')
<section class="content-hero fade-in">

    <!-- HERO -->
    <div class="contact-title-wrapper fade-section">
        <h1 class="contact-title">
            Contact <span class="gradient">SharpLync</span>
        </h1>
        <p class="contact-subtitle">
            We're here to help your business get IT right. Get in touch to discuss your technology needs.
        </p>
    </div>


    <!-- CONTACT BLOCKS -->
    <div class="details-grid-wrapper fade-section">
        <h3 class="grid-heading">Connect with Us</h3>

        <div class="details-grid">

            <!-- Email -->
            <div class="detail-item">
                <div class="icon-wrapper">
                    <img src="{{ asset('images/email.png') }}" alt="Email Icon" class="detail-icon">
                </div>
                <h4>Email Support</h4>
                <p>For all service enquiries and general questions.</p>
                <a href="mailto:info@sharplync.com.au" class="detail-link">
                    info@sharplync.com.au
                </a>
            </div>

            <!-- LinkedIn -->
            <div class="detail-item">
                <div class="icon-wrapper">
                    <img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn Icon" class="detail-icon">
                </div>
                <h4>LinkedIn</h4>
                <p>Follow our updates and professional insights.</p>
                <a href="https://www.linkedin.com/company/sharplync" target="_blank" class="detail-link">
                    Connect with our page
                </a>
            </div>

            <!-- Facebook -->
            <div class="detail-item">
                <div class="icon-wrapper">
                    <img src="{{ asset('images/facebook.png') }}" alt="Facebook Icon" class="detail-icon">
                </div>
                <h4>Facebook</h4>
                <p>See our latest news and announcements.</p>
                <a href="https://www.facebook.com/SharpLync" target="_blank" class="detail-link">
                    Join our community
                </a>
            </div>

        </div>
    </div>


    <!-- FORM -->
    <div class="contact-form-card fade-section">
        <h3 class="form-heading">Send Us a Message</h3>

        <form action="/submit-contact" method="POST" class="contact-form">
            @csrf

            <!-- Hidden honeypot -->
            <div class="form-group full-width" style="display:none; height:0; overflow:hidden;">
                <input type="text" name="address_bot_trap" value="">
            </div>

            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required placeholder="John Doe">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="name@company.com">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number (Optional)</label>
                <input type="tel" id="phone" name="phone" placeholder="04XX XXX XXX">
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required placeholder="Inquiry about Managed IT">
            </div>

            <div class="form-group full-width">
                <label for="message">Your Message</label>
                <textarea id="message" name="message" rows="6" required placeholder="Tell us about your IT challenge or project..."></textarea>
            </div>

            <div class="form-group full-width">
                <button type="submit" class="submit-btn">Send Message</button>
            </div>

        </form>
    </div>

</section>
@endsection
