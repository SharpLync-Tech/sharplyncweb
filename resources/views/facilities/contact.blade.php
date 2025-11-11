@extends('layouts.base')

@section('title', 'SharpLync Facilities | Contact')

@section('content')
<section class="hero">
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-facilities.jpg') }}" alt="SharpLync Facilities Contact Background">
  </div>

  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo">

  <div class="hero-text">
    <h1>Get In Touch</h1>
    <p>Ready to optimize your facilities? Contact our team for personalized support from the Granite Belt.</p>
  </div>
</section>

<section class="info-section fade-section">
  <div class="info-card">
    <h3>Contact Information</h3>
    <p><strong>Email:</strong> facilities@sharplync.com.au<br>
    <strong>Phone:</strong> (07) 4667 1234<br>
    <strong>Address:</strong> 123 Main St, Stanthorpe QLD 4380</p>

    <h3>Send Us a Message</h3>
    <form action="/facilities/contact" method="POST" class="contact-form">
      @csrf
      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="subject">Subject</label>
        <select id="subject" name="subject" required>
          <option value="">Select a topic</option>
          <option value="facility-management">Facility Management</option>
          <option value="fleet-management">Fleet Management</option>
          <option value="building-automation">Building Automation</option>
          <option value="general">General Inquiry</option>
        </select>
      </div>
      <div class="form-group">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="5" required></textarea>
      </div>
      <button type="submit" class="submit-btn">Send Message</button>
    </form>
  </div>
</section>

<style>
/* Minimal inline for form; move to facilities.css if needed */
.contact-form { max-width: 500px; margin: 0 auto; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; }
.submit-btn { background: #2CBFAE; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; }
.submit-btn:hover { background: #35E0C2; }
</style>
@endsection