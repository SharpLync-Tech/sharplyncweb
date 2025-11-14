<!-- ===================== -->
<!-- Works on Mobile. Desktop My Story not formatted correctly -->
<!-- ===================== -->

@extends('layouts.about-base')

@section('title', 'SharpLync | About')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="content-hero fade-in">

  <div class="content-header fade-section">
      <h1 class="about-title">
          <span class="about-main-word">About</span>
          <span class="about-brand">SharpLync</span>
      </h1>

      <p class="about-subtitle">
          From the Granite Belt to the Cloud, bridging the gap between people and technology with old school support and modern results.
      </p>
  </div>

  <!-- ===================== -->
  <!-- My Story Section v3 -->
  <!-- ===================== -->
  <div class="content-card fade-section">
    <h3>My Story: From Tools to Technology</h3>

    <div id="storyIntro">
      <p>My journey into technology didn’t start in a lab or an office. It started with a set of tools, cables, and a good dose of curiosity.</p>

      <p>I began my career as an Electrical Fitter, learning the value of precision, safety, and doing things properly the first time. From there, my interest naturally shifted toward the growing world of data and communication, where I started working on network cabling, PABX phone systems, and fibre optics. It was hands-on, practical work that taught me how every wire and connection plays a part in keeping a business running smoothly.</p>

      <p>As technology evolved, so did I. I moved into the IT world, working as a Computer Technician for Harvey Norman, helping people get their systems up and running, and just as importantly, making sure they actually understood how to use them.</p>
    </div>

    <div id="storyFull" class="collapsed">
      <p>That experience showed me how much people appreciate honest, down-to-earth support, the kind that doesn’t rely on jargon.</p>

      <p>In the early 2000s, I took a leap and started my own business. It grew quickly, built on trust, reliability, and word-of-mouth, the old-fashioned way. Things went so well that the business was amalgamated into a larger company, giving me the chance to see how IT operates at scale.</p>

      <p>From there, I stepped into the corporate world as a Systems Administrator, managing infrastructure and supporting teams that relied on technology every day. That role led to a new chapter, one that would last over a decade in education.</p>

      <h3>Establishing Expertise at Scale</h3>
      <p>During my time working for a large school network, I helped upgrade two existing campuses and build the IT infrastructure for four new ones, everything from networking and servers to Wi-Fi, printers, cloud infrastructure, and device management. It was a massive challenge, but it shaped the way I see technology: not just as wires and code, but as something that connects people and helps them learn, grow, and succeed.</p>

      <h3>The Launch of SharpLync: Seizing an Opportunity</h3>
      <p>After more than a decade managing complex, multi-site infrastructure, I had a unique vantage point. I saw clearly what high-level, practical IT support looks like, and what was often missing for growing businesses. It became obvious that many organisations struggle to access proven, enterprise-level expertise without the massive price tag. They deserve better than generic fixes.</p>

      <p>Launching <strong>SharpLync</strong> was a proactive decision. It was the moment to take my entire range of skills—from the electrical fitter's precision to the system administrator's strategic vision—and focus them entirely on helping businesses get IT right.</p>

      <p>I believe in old school support with modern results: being reliable, approachable, and genuinely invested in helping people make the most of their technology. Because at the end of the day, it’s not just about systems, it’s about people.</p>
    </div>

    <button id="toggleStory" class="toggle-btn">Continue My Story +</button>
  </div>

  <!-- ===================== -->
  <!-- Testimonials Section -->
  <!-- ===================== -->
  <section class="testimonials-section fade-section">
    <h3>What People Say</h3>

    <div class="testimonial-wrapper">
      <div class="testimonial-container">
        @forelse($testimonials as $t)
          @php
            $who = trim(
              ($t->customer_position ? $t->customer_position : '') .
              (($t->customer_position && $t->customer_company) ? ' — ' : '') .
              ($t->customer_company ? $t->customer_company : '')
            );

            $preview = Str::limit(strip_tags($t->testimonial_text), 320);
          @endphp

          <div
              class="testimonial {{ $loop->first ? 'active' : '' }}"
              data-fulltext="{{ e($t->testimonial_text) }}"
              data-name="{{ e($t->customer_name) }}"
              data-who="{{ e($who) }}"
          >
            <div class="testimonial-meta">
              <h4>{{ $t->customer_name }}</h4>
              @if($who)
                <p class="testimonial-role">{{ $who }}</p>
              @endif
            </div>

            <p class="testimonial-preview">"{{ $preview }}"</p>

            <button type="button" class="testimonial-read-more">
              Read more…
            </button>
          </div>
        @empty
          <div class="testimonial active" data-fulltext="Jannie is one of the most dependable and dedicated IT professionals I’ve worked with." data-name="Former Principal" data-who="The Industry School">
            <div class="testimonial-meta">
              <h4>Former Principal</h4>
              <p class="testimonial-role">The Industry School</p>
            </div>
            <p class="testimonial-preview">
              "Jannie is one of the most dependable and dedicated IT professionals I’ve worked with."
            </p>
            <button type="button" class="testimonial-read-more">
              Read more…
            </button>
          </div>
        @endforelse
      </div>

      <div class="testimonial-dots" aria-label="Testimonial navigation"></div>
    </div>
  </section>

  <div id="testimonialModal" class="testimonial-modal" aria-hidden="true">
    <div class="testimonial-modal-backdrop"></div>

    <div class="testimonial-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="testimonialModalName">
      <button type="button" class="testimonial-modal-close" aria-label="Close testimonial">&times;</button>

      <div class="testimonial-modal-inner">
        <div class="testimonial-modal-quote">“</div>

        <p class="testimonial-modal-text" id="testimonialModalText"></p>

        <div class="testimonial-modal-separator"></div>

        <p class="testimonial-modal-name" id="testimonialModalName"></p>
        <p class="testimonial-modal-role" id="testimonialModalRole"></p>
      </div>
    </div>
  </div>
</section>

@push('scripts')
{{-- JS unchanged --}}
@endpush
@endsection
