@extends('layouts.content')

@section('title', 'SharpLync | Testimonials')

@section('content')
<section class="content-hero fade-in">
  <div class="content-header fade-section">
    <h1>Customer <span class="highlight">Testimonials</span></h1>
    <p>Real words from the people and organisations we’ve supported.</p>
  </div>

  <div class="fade-section" style="margin-top:22px;">
    {{-- Scoped layout for this page only (keeps site CSS untouched) --}}
    <style>
      .testimonials-stack{
        max-width: 940px;      /* similar width to About card */
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
      }
      .testimonial-card blockquote{margin:0; line-height:1.7}
      .testimonial-card .who{
        display:block; margin-top:12px; opacity:.85;
      }
      .testimonial-card .rating{margin-top:10px; font-weight:700; letter-spacing:.5px}
      @media (max-width: 980px){
        .testimonials-stack{padding: 0 16px;}
      }
    </style>

    <div class="testimonials-stack">
      @forelse($testimonials as $t)
        @php
          $who = trim(($t->customer_name ?: '') .
                      (($t->customer_position || $t->customer_company) ? ' — ' : '') .
                      ($t->customer_position ?: '') .
                      (($t->customer_position && $t->customer_company) ? ' — ' : '') .
                      ($t->customer_company ?: ''));
          $stars = $t->rating ? str_repeat('★', (int)$t->rating) . str_repeat('☆', 5 - (int)$t->rating) : null;
        @endphp

        <article class="content-card testimonial-card">
          <blockquote>“{{ $t->testimonial_text }}”</blockquote>
          <span class="who">{{ $who }}</span>
          @if($stars)
            <div class="rating" aria-label="Rating: {{ $t->rating }} out of 5">{{ $stars }}</div>
          @endif
        </article>
      @empty
        <article class="content-card">
          <p>No testimonials available yet. Please check back soon.</p>
        </article>
      @endforelse
    </div>
  </div>
</section>
@endsection