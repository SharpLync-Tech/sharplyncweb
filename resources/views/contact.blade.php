@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('title', 'Contact SharpLync')

@section('content')

<section class="contact-hero">
    <h1 class="contact-title">
        <span class="white-text">Contact</span>
        <span class="brand-gradient">SharpLync</span>
    </h1>
    <p class="contact-subtitle">
        Choose the best way to reach our team. We're here to help your business get IT right.
    </p>
</section>

{{-- TALK TO US --}}
<section class="contact-info-section fade-in">
    <div>
        <h2 class="section-heading">Talk to Us</h2>
        <p class="section-sub">Need help now? Use one of these direct support channels.</p>

        <div class="contact-info-grid talk">

            {{-- Email --}}
            <div class="info-card">
                <div class="info-icon">
                    <img src="{{ asset('images/email.png') }}" alt="Email" />
                </div>
                <h4>Email Support</h4>
                <p>For general enquiries and service questions.</p>
                <a href="mailto:info@sharplync.com.au" class="info-link">info@sharplync.com.au</a>
            </div>

            {{-- Phone --}}
            <div class="info-card">
                <div class="info-icon">
                    <img src="{{ asset('images/phone.png') }}" alt="Phone" />
                </div>
                <h4>Call Us</h4>
                <p>Fast support for urgent enquiries.</p>
                <a href="tel:0492014463" class="info-link">0492 014 463</a>
            </div>

            {{-- WhatsApp --}}
            <div class="info-card">
                <div class="info-icon">
                    <img src="{{ asset('images/whatsapp.png') }}" alt="WhatsApp" />
                </div>
                <h4>WhatsApp</h4>
                <p>Quick messaging for fast support and updates.</p>
                <a href="https://wa.me/message/K7U44ZE6X53LH1" target="_blank" class="info-link">
                    Chat with us
                </a>
            </div>

        </div>
    </div>
</section>

{{-- CONNECT WITH US --}}
<section class="contact-info-section fade-in">
    <div>
        <h2 class="section-heading">Connect With Us</h2>
        <p class="section-sub">Stay in the loop with SharpLync news, updates and insights.</p>

        <div class="contact-info-grid connect">

            {{-- LinkedIn --}}
            <div class="info-card">
                <div class="info-icon">
                    <img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn" />
                </div>
                <h4>LinkedIn</h4>
                <p>Updates, announcements and professional insights.</p>
                <a href="https://www.linkedin.com/company/sharplync" target="_blank" class="info-link">
                    Connect with us
                </a>
            </div>

            {{-- Facebook --}}
            <div class="info-card">
                <div class="info-icon">
                    <img src="{{ asset('images/facebook.png') }}" alt="Facebook" />
                </div>
                <h4>Facebook</h4>
                <p>See our latest news and community updates.</p>
                <a href="https://www.facebook.com/SharpLync" target="_blank" class="info-link">
                    Join our community
                </a>
            </div>

        </div>
    </div>
</section>

{{-- CONTACT FORM --}}
<section class="contact-form-section fade-in">
    <div class="contact-form-card">
        <h3 class="form-title">Send Us a Message</h3>

        <form method="POST" action="{{ route('contact.submit') }}">
            @csrf

            <input type="text" name="address_bot_trap" style="display:none;">
            <input type="hidden" id="recaptcha_token" name="recaptcha_token">

            <div class="form-row">
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone Number (Optional)</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}">
                    @error('phone') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required>
                    @error('subject') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group full">
                <label>Your Message</label>
                <textarea rows="6" name="message" required>{{ old('message') }}</textarea>
                @error('message') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group full">
                <button type="submit" class="submit-btn">Send Message</button>
            </div>

        </form>
    </div>
</section>

{{-- SUCCESS MODAL --}}
<div id="contactSuccessModal" class="contact-modal-overlay" style="display:none;">
    <div class="contact-modal">
        <div class="contact-modal-icon">✓</div>
        <h3 class="contact-modal-title">Message Sent!</h3>
        <p class="contact-modal-text">We’ll get back to you shortly.</p>
        <button id="closeContactModal" class="contact-modal-btn">OK</button>
    </div>
</div>

@endsection

@push('scripts')
@if(config('services.recaptcha.key'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.key') }}"></script>
<script>
    grecaptcha.ready(function () {
        grecaptcha.execute('{{ config('services.recaptcha.key') }}', {action: 'submit_contact'})
            .then(function (token) {
                document.getElementById('recaptcha_token').value = token;
            });
    });
</script>
@endif

@if(session('success'))
<script>
document.addEventListener("DOMContentLoaded", () => {
    const overlay = document.getElementById("contactSuccessModal");
    const closeBtn = document.getElementById("closeContactModal");

    overlay.style.display = "flex";

    const close = () => {
        overlay.style.animation = "modalFadeOut 0.4s forwards";
        setTimeout(() => overlay.style.display = "none", 400);
    };

    closeBtn.addEventListener("click", close);
    overlay.addEventListener("click", e => { if (e.target === overlay) close(); });

    setTimeout(close, 3000);
});
</script>
@endif
@endpush
