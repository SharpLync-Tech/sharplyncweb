@extends('layouts.contact-base')

@section('title', 'SharpLync | Contact Us')

@section('content')
<section class="content-hero fade-in">

    {{-- ===================== --}}
    {{-- Contact Us Title --}}
    {{-- ===================== --}}
    <div class="contact-title-wrapper fade-section">
        <h1 class="contact-title">
            Contact <span class="gradient">SharpLync</span>
        </h1>
        <p class="contact-subtitle">
            We're here to help your business get IT right. Get in touch to discuss your technology needs.
        </p>
    </div>

    {{-- =============================================== --}}
    {{-- Contact Details Card --}}
    {{-- =============================================== --}}
    <div class="contact-details-card fade-section">
        <h3>Connect with Us</h3>
        <div class="details-grid">

            <div class="detail-item">
                <img src="{{ asset('images/email-icon-placeholder.png') }}" alt="Email Icon" class="detail-icon">
                <h4>Email Support</h4>
                <a href="mailto:info@sharplync.com.au" class="detail-link">info@sharplync.com.au</a>
                <p>For all service enquiries and general questions.</p>
            </div>

            <div class="detail-item">
                <img src="{{ asset('images/linkedin-icon-placeholder.png') }}" alt="LinkedIn Icon" class="detail-icon">
                <h4>LinkedIn</h4>
                <a href="https://www.linkedin.com/company/sharplync" target="_blank" class="detail-link">Connect with our page</a>
                <p>Follow our updates and professional insights.</p>
            </div>

            <div class="detail-item">
                <img src="{{ asset('images/facebook-icon-placeholder.png') }}" alt="Facebook Icon" class="detail-icon">
                <h4>Facebook</h4>
                <a href="https://www.facebook.com/SharpLync" target="_blank" class="detail-link">Join our community</a>
                <p>See our latest news and announcements.</p>
            </div>

        </div>
    </div>


    {{-- =============================================== --}}
    {{-- Contact Form Section --}}
    {{-- =============================================== --}}
    <div class="contact-form-card fade-section">
        <h3>Send Us a Message</h3>
        <form action="/submit-contact" method="POST" class="contact-form">
            @csrf {{-- Laravel CSRF protection --}}

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