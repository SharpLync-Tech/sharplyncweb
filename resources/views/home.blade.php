<!-- 
  Page: home.blade.php
  Version: v2.4.0
  Last updated: 15 Jan 2026
  Description: Structured hero card grid (6 cards + full-width pricing card)
-->

@extends('layouts.base')

@section('title', 'IT Support Stanthorpe & Granite Belt | SharpLync')
@section('meta_description', 'Local IT support for Stanthorpe and the Granite Belt. SharpLync helps businesses with computer repairs, Microsoft 365, cybersecurity, Wi-Fi, backups and remote support.')
@section('canonical', rtrim(config('seo.site_url'), '/') . '/')

@section('content')

<section class="hero">

  {{-- CPU background --}}
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  {{-- Logo --}}
  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo">

  {{-- Hero text --}}
  <div class="hero-text">
    <h1>Local IT Support for<br><span class="highlight">Stanthorpe & the Granite Belt</span></h1>
    <p>
      Practical business IT support, computer repairs, cloud, cybersecurity and networking—
      delivered locally or through secure remote support.
    </p>
  </div>

  {{-- =========================
       HERO CARDS WRAPPER
  ========================== --}}
  <div class="hero-cards fade-section">

    {{-- ===== TOP 6 INFO CARDS ===== --}}
    <div class="hero-card-grid">

      <div class="tile transparent">
        <img src="{{ asset('images/about.png') }}" alt="About SharpLync" class="tile-icon">
        <h3>About SharpLync</h3>
        <p>
          We’re your tech partner. Reliable people, smarter systems,
          and genuine care for getting IT right.
        </p>
        <a href="/about" class="learn-more">Learn more</a>
      </div>

      <div class="tile transparent">
        <img src="{{ asset('images/what_we_do.png') }}" alt="What We Do" class="tile-icon">
        <h3>What We Do</h3>
        <p>
          Cloud, security, networking, and on-site support —
          real solutions that keep your business moving.
        </p>
        <a href="/services" class="learn-more">Learn more</a>
      </div>

      <div class="tile transparent">
        <img src="{{ asset('images/contact_us.png') }}" alt="Contact Us" class="tile-icon">
        <h3>Contact Us</h3>
        <p>
          Need advice or support? Local, friendly help with
          nationwide reach. No fuss, no call centres.
        </p>
        <a href="/contact" class="learn-more">Learn more</a>
      </div>

      <div class="tile transparent">
        <h3>Cyber Security</h3>
        <p>
          Protection that actually makes sense. We secure
          your systems without locking you out of your own business.
        </p>
        <a href="{{ route('services') }}#cybersecurity" class="learn-more">Learn more</a>
      </div>

      <div class="tile transparent">
        <h3>Cloud & Microsoft 365</h3>
        <p>
          Email, files, and collaboration done right —
          simple, secure, and fully supported.
        </p>
        <a href="{{ route('services') }}#cloud-m365" class="learn-more">Learn more</a>
      </div>

      <div class="tile transparent">
        <h3>Local IT Support</h3>
        <p>
          On-site when you need it, remote when you don’t.
          Real people who actually answer the phone.
        </p>
        <a href="{{ route('it-support.stanthorpe') }}" class="learn-more">Learn more</a>
      </div>

    </div>

    {{-- ===== FULL-WIDTH PRICING CARD ===== --}}
    <div class="hero-card-pricing">

      <div class="tile pricing-tile">
        <h3>Simple, Affordable IT Support</h3>
        <p>
          Clear pricing, no surprise invoices, no hidden extras.
          Professional IT support that costs less than most businesses
          spend fixing preventable problems.
        </p>
        <a href="/contact" class="learn-more primary">Contact us for Pricing</a>
      </div>

    </div>

  </div>

  <section class="home-local-intro fade-section" aria-labelledby="home-local-heading">
    <h2 id="home-local-heading">IT help for businesses in Stanthorpe and surrounding areas</h2>
    <p>
      SharpLync supports businesses across Stanthorpe, the Granite Belt and the wider Southern Downs.
      Get help with day-to-day technology problems, Microsoft 365, computer faults, cybersecurity,
      business Wi-Fi, backups and sensible IT planning.
    </p>
    <div class="home-local-links">
      <a href="{{ route('it-support.stanthorpe') }}" class="learn-more primary">IT Support Stanthorpe</a>
      <a href="{{ route('computer-repairs.stanthorpe') }}" class="learn-more">Computer Repairs Stanthorpe</a>
    </div>
  </section>

</section>

{{-- =========================
     PAGE-SCOPED STYLES
========================== --}}
<style>

.hero-cards {
  max-width: 1200px;
  margin: 3rem auto 0;
  padding: 0 1rem;
}

/* 6-card grid */
.hero-card-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

/* Pricing card row */
.hero-card-pricing {
  margin-top: 2rem;
}

.hero-card-pricing .pricing-tile {
  width: 100%;
  text-align: center;
}

.home-local-intro {
  max-width: 1000px;
  margin: 3rem auto 0;
  padding: 2rem;
  text-align: center;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.96);
  color: #17324d;
}

.home-local-intro h2 { margin-top: 0; }
.home-local-intro p { max-width: 820px; margin: 0 auto 1.5rem; line-height: 1.7; }
.home-local-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; }

/* Responsive */
@media (max-width: 900px) {
  .hero-card-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .hero-card-grid {
    grid-template-columns: 1fr;
  }
}

</style>

@endsection
