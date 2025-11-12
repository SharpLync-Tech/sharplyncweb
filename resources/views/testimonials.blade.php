@extends('layouts.content')

@section('title', 'SharpLync | Testimonials')

@section('content')
<section class="content-hero fade-in">
  <div class="content-header fade-section">
    <h1>Customer <span class="highlight">Testimonials</span></h1>
    <p>Real words from the people and organisations we’ve supported.</p>
  </div>

  <div class="fade-section" style="margin-top:22px;">
    <style>
          /* Container */
          .testimonial-carousel {
              position: relative;
              max-width: 940px;
              margin: 0 auto;
              overflow: hidden;
              padding: 10px;
              min-height: 460px; /* keeps steady height */
            }

            /* Stack all slides on top of each other, same width */
            .testimonial-slide {
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;                /* ✅ ensures full width */
              opacity: 0;
              z-index: 1;
              transition: opacity 1s ease-in-out;
            }

            /* Active slide fully visible */
            .testimonial-slide.active {
              opacity: 1;
              z-index: 2;
            }

            /* Inside the card */
            .testimonial-card {
              width: 100%;
              max-width: 940px;
              margin: 0 auto;
            }

            .testimonial-card blockquote {
              margin: 0;
              line-height: 1.7;
            }

            .testimonial-card .who {
              display: block;
              margin-top: 12px;
              opacity: 0.85;
            }

            .testimonial-card .rating {
              margin-top: 10px;
              font-weight: 700;
              letter-spacing: .5px;
            }

            /* Pagination dots */
            .testimonial-dots {
              text-align: center;
              margin-top: 22px;
            }

            .dot {
              height: 12px;
              width: 12px;
              margin: 0 5px;
              background-color: transparent;
              border: 2px solid #104946;
              border-radius: 50%;
              display: inline-block;
              cursor: pointer;
              transition: background-color 0.3s;
            }

            .dot.active {
              background-color: #104946;
            }

            @media (max-width: 980px){
              .testimonial-card { padding: 0 10px; }
            }

        </style>

        <div class="testimonial-carousel" id="testimonialCarousel">
          @forelse($testimonials as $t)
            @php
              $who = trim(($t->customer_name ?: '') .
                          (($t->customer_position || $t->customer_company) ? ' — ' : '') .
                          ($t->customer_position ?: '') .
                          (($t->customer_position && $t->customer_company) ? ' — ' : '') .
                          ($t->customer_company ?: ''));
              $stars = $t->rating ? str_repeat('★', (int)$t->rating) . str_repeat('☆', 5 - (int)$t->rating) : null;
            @endphp

            <div class="testimonial-slide {{ $loop->first ? 'active' : '' }}">
              <article class="content-card testimonial-card">
                <blockquote>“{{ $t->testimonial_text }}”</blockquote>
                <span class="who">{{ $who }}</span>
                @if($stars)
                  <div class="rating" aria-label="Rating: {{ $t->rating }} out of 5">{{ $stars }}</div>
                @endif
              </article>
            </div>
          @empty
            <div class="testimonial-slide active">
              <article class="content-card">
                <p>No testimonials available yet. Please check back soon.</p>
              </article>
            </div>
          @endforelse

          <div class="testimonial-dots" id="testimonialDots">
            @foreach($testimonials as $index => $t)
              <span class="dot {{ $loop->first ? 'active' : '' }}" data-index="{{ $index }}"></span>
            @endforeach
          </div>
        </div>

        <script>
          const slides = document.querySelectorAll('.testimonial-slide');
          const dots = document.querySelectorAll('#testimonialDots .dot');
          const carousel = document.getElementById('testimonialCarousel');
          let currentIndex = 0;
          let autoSlide;

          function showSlide(index) {
            slides.forEach((slide, i) => {
              slide.classList.toggle('active', i === index);
              dots[i]?.classList.toggle('active', i === index);
            });
            currentIndex = index;
          }

          function nextSlide() {
            const next = (currentIndex + 1) % slides.length;
            showSlide(next);
          }

          function startAutoSlide() {
            autoSlide = setInterval(nextSlide, 6000);
          }

          function stopAutoSlide() {
            clearInterval(autoSlide);
          }

          // Dots click
          dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
          });

          // Pause on hover
          carousel.addEventListener('mouseenter', stopAutoSlide);
          carousel.addEventListener('mouseleave', startAutoSlide);

          startAutoSlide();
        </script>

  </div>
</section>

@endsection