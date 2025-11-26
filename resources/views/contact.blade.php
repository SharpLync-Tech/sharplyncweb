@extends('layouts.contact-base')

@section('title', 'SharpLync | Contact Us')

@section('content')
<section class="content-hero fade-in">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="contact-alert contact-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="contact-alert contact-alert-error">
            {{ session('error') }}
        </div>
    @endif

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
    {{-- Contact Details Grid (FIXED 3-COLUMN LAYOUT) --}}
    {{-- =============================================== --}}
    <div class="details-grid-wrapper fade-section">
        <h3 class="grid-heading">Connect with Us</h3>

        <div class="details-grid">

            {{-- Block 1: Email Support --}}
            <div class="detail-item">
                <div class="icon-wrapper">
                    <img src="{{ asset('images/email.png') }}" alt="Email Icon" class="detail-icon">
                </div>
                <h4>Email Support</h4>
                <p>For all service enquiries and general questions.</p>
                <a href="mailto:info@sharplync.com.au" class="detail-link">info@sharplync.com.au</a>
            </div>

            {{-- Block 2: LinkedIn --}}
            <div class="detail-item">
                <div class="icon-wrapper">
                    <img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn Icon" class="detail-icon">
                </div>
                <h4>LinkedIn</h4>
                <p>Follow our updates and professional insights.</p>
                <a href="https://www.linkedin.com/company/sharplync" target="_blank" class="detail-link">Connect with our page</a>
            </div>

            {{-- Block 3: Facebook --}}
            <div class="detail-item">
                <div class="icon-wrapper">
                    <img src="{{ asset('images/facebook.png') }}" alt="Facebook Icon" class="detail-icon">
                </div>
                <h4>Facebook</h4>
                <p>See our latest news and announcements.</p>
                <a href="https://www.facebook.com/SharpLync" target="_blank" class="detail-link">Join our community</a>
            </div>

        </div>
    </div>

    {{-- =============================================== --}}
    {{-- Contact Form Section (Styled like Login Screen) --}}
    {{-- =============================================== --}}
    <div class="contact-form-card fade-section">
        <h3 class="form-heading">Send Us a Message</h3>

        <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
            @csrf

            {{-- Honeypot field for bot protection --}}
            <div class="form-group full-width" style="display:none; height:0; overflow:hidden;">
                <label for="address_bot_trap">Do not fill this field (Bot Trap)</label>
                <input type="text" id="address_bot_trap" name="address_bot_trap" value="">
            </div>

            {{-- reCAPTCHA token --}}
            <input type="hidden" name="recaptcha_token" id="recaptcha_token">

            <div class="form-group">
                <label for="name">Your Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="John Doe">
                @error('name')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    placeholder="name@company.com">
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone Number (Optional)</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="04XX XXX XXX">
                @error('phone')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="{{ old('subject') }}"
                    required
                    placeholder="Inquiry about Managed IT">
                @error('subject')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="message">Your Message</label>
                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    required
                    placeholder="Tell us about your IT challenge or project...">{{ old('message') }}</textarea>
                @error('message')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group full-width">
                <button type="submit" class="submit-btn">Send Message</button>
            </div>
        </form>
    </div>

</section>

{{-- Success Modal --}}
<div id="contactSuccessModal" class="contact-modal-overlay" style="display:none;">
    <div class="contact-modal">
        <div class="contact-modal-icon">
            ✓
        </div>
        <h3 class="contact-modal-title">Message Sent!</h3>
        <p class="contact-modal-text">We’ll get back to you shortly.</p>

        <button id="closeContactModal" class="contact-modal-btn">
            OK
        </button>
    </div>
</div>

@endsection

@push('scripts')
    @if(config('services.recaptcha.key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.key') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.ready(function () {
                        grecaptcha.execute('{{ config('services.recaptcha.key') }}', { action: 'submit_contact' })
                            .then(function (token) {
                                var el = document.getElementById('recaptcha_token');
                                if (el) {
                                    el.value = token;
                                }
                            });
                    });
                }
            });
        </script>
    @endif

    {{-- Success Modal Logic --}}
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('contactSuccessModal');
    const closeBtn = document.getElementById('closeContactModal');

    if (overlay && closeBtn) {
        overlay.style.display = 'flex';

        // Manual close
        closeBtn.addEventListener('click', () => {
            overlay.style.animation = "modalFadeOut 0.4s forwards";
            setTimeout(() => { overlay.style.display = 'none'; }, 400);
        });

        // Close by clicking background
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.animation = "modalFadeOut 0.4s forwards";
                setTimeout(() => { overlay.style.display = 'none'; }, 400);
            }
        });

        // Auto-dismiss after 3 seconds ⭐
        setTimeout(() => {
            overlay.style.animation = "modalFadeOut 0.4s forwards";
            setTimeout(() => { overlay.style.display = 'none'; }, 400);
        }, 3000);
    }
});
</script>
@endif
@endpush


