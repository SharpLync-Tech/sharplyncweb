@extends('layouts.base')

@section('title', 'SharpLync Facilities | Home')

@section('content')
<section class="hero">
  <!-- Facilities-themed image (replacing CPU bg) -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-facilities.jpg') }}" alt="SharpLync Facilities Background">
  </div>

  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo">

  <div class="hero-text">
    <h1>Smart Facilities.<br><span class="highlight">Local Reliability.</span></h1>
    <p>From the Granite Belt to nationwide sites — managing facilities, fleets, and automation with precision and care. Integrated with SharpLync's trusted IT backbone for seamless operations.</p>
  </div>

  <div class="hero-cards fade-section">
    <div class="tile transparent">
      <img src="{{ asset('images/facilities/facility-mgmt.png') }}" alt="Facility Management" class="tile-icon">
      <h3>Facility Management</h3>
      <p>Track sites, projects, and budgets seamlessly. From site details to asset oversight, keep everything organized and on schedule.</p>
      <a href="/facilities/services/management" class="learn-more">Learn more →</a>
    </div>

    <div class="tile transparent">
      <img src="{{ asset('images/facilities/fleet-mgmt.png') }}" alt="Fleet Management" class="tile-icon">
      <h3>Fleet Management</h3>
      <p>Efficient vehicle and driver management, from registrations and VIN tracking to maintenance schedules and odometer monitoring.</p>
      <a href="/facilities/services/fleet" class="learn-more">Learn more →</a>
    </div>

    <div class="tile transparent">
      <img src="{{ asset('images/facilities/automation.png') }}" alt="Building Automation" class="tile-icon">
      <h3>Building Automation</h3>
      <p>Automate tasks and assets for efficiency and compliance. Streamline maintenance, warranties, and workflows across your facilities.</p>
      <a href="/facilities/services/automation" class="learn-more">Learn more →</a>
    </div>
  </div>
</section>

<section class="info-section fade-section">
  <div class="info-card">
    <h3>Why Choose Us</h3>
    <ul>
      <li><strong>Experience:</strong> Decades of hands-on expertise in multi-site infrastructure, from Granite Belt projects to nationwide fleets.</li>
      <li><strong>Transparency:</strong> Clear reporting on assets, tasks, and budgets – no hidden fees, just reliable insights.</li>
      <li><strong>Integration:</strong> Seamless tie-in with SharpLync's core IT: cloud backups for vehicle data, secure networks for site automation.</li>
    </ul>
  </div>
</section>
@endsection