<!-- @extends('layouts.about-base') -->
@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('title', 'SharpLync | About')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="content-hero fade-in">

  {{-- ===================== --}}
  {{-- About SharpLync Title --}}
  {{-- ===================== --}}
  <div class="about-title-wrapper fade-section">
      <h1 class="about-title">
          About <span class="gradient">SharpLync</span>
      </h1>      
  </div>

  {{-- =============================================== --}}
  {{-- My Story — Split Layout White Card (Option A)   --}}
  {{-- =============================================== --}}
  <div class="story-card-split fade-section">

      {{-- Left image (hidden on mobile via CSS) --}}
      <div class="story-image">
          <img src="{{ asset('images/mystory.png') }}" alt="My Story">
      </div>

      {{-- Right column: script title + content --}}
      <div class="story-text">
          <div class="story-script-title">My Story</div>
          <h3>From Tools to Technology</h3>

          <div id="storyIntro">
              <p>My journey into technology didn’t start in a lab or an office. It started with a set of tools, cables, and a good dose of curiosity.</p>

              <p>I began my career as an Electrical Fitter, learning the value of precision, safety, and doing things properly the first time. From there, my interest naturally shifted toward the growing world of data and communication, where I started working on network cabling, PABX phone systems, and fibre optics. It was hands-on, practical work that taught me how every wire and connection plays a part in keeping a business running smoothly.</p>

              <p>As technology evolved, so did I. I moved into the IT world, working as a Computer Technician for Harvey Norman, helping people get their systems up and running, and just as importantly, making sure they actually understood how to use them.</p>
          </div>

          {{-- Hidden full story --}}
          <div id="storyFull" class="collapsed">
              <p>That experience showed me how much people appreciate honest, down-to-earth support, the kind that doesn’t rely on jargon.</p>

              <p>In the early 2000s, I took a leap and started my own business. It grew quickly, built on trust, reliability, and word-of-mouth, the old-fashioned way. Things went so well that the business was amalgamated into a larger company, giving me the chance to see how IT operates at scale.</p>

              <p>From there, I stepped into the corporate world as a Systems Administrator, managing infrastructure and supporting teams that relied on technology every day. That role led to a new chapter, one that would last over a decade in education.</p>

              <h3>Establishing Expertise at Scale</h3>
              <p>During my time working for a large school network, I helped upgrade two existing campuses and build the IT infrastructure for four new ones, everything from networking and servers to Wi-Fi, printers, cloud infrastructure, and device management. It was a massive challenge, but it shaped the way I see technology: not just as wires and code, but as something that connects people and helps them learn, grow, and succeed.</p>

              <h3>The Launch of SharpLync: Seizing an Opportunity</h3>
              <p>After more than a decade managing complex, multi-site infrastructure, I had a unique vantage point. I saw clearly what high-level, practical IT support looks like, and what was often missing for growing businesses. It became obvious that many organisations struggle to access proven, enterprise-level expertise without the massive price tag. They deserve better than generic fixes.</p>

              <p>Launching <strong>SharpLync</strong> was a proactive decision. It was the moment to take my entire range of skills—from the electrical fitter's precision to the system administrator's strategic vision—and focus them entirely on helping businesses get IT right.</p>

              <p>I believe in Straightforward Support support with modern results: being reliable, approachable, and genuinely invested in helping people make the most of their technology. Because at the end of the day, it’s not just about systems, it’s about people.</p>
          </div>
          <hr class="story-divider">
          <div class="story-signature">
              <div class="sig-name">Jannie Brits</div>
              <div class="sig-role">Founder & Lead Engineer</div>

              <a href="https://www.linkedin.com/in/jcbrits/" 
                class="sig-linkedin" target="_blank">
                <img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn">
              </a>
          </div>


          <button id="toggleStory" class="toggle-btn">Continue My Story...</button>
      </div>
  </div>
@endsection
