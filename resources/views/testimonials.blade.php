@extends('layouts.testimonials-base')

@section('title', 'SharpLync | Testimonials')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="testimonials-page">

    {{-- Page heading --}}
    <div class="testimonials-title">
        <h1>Customer Testimonials</h1>
        <p>Real words from the people and organisations we’ve supported.</p>
    </div>

    {{-- Dots above the carousel --}}
    <div class="tl-dots" id="tlDots">
        @foreach($testimonials as $index => $t)
            <span class="tl-dot {{ $loop->first ? 'active' : '' }}" data-index="{{ $index }}"></span>
        @endforeach
    </div>

    {{-- Carousel --}}
    <div class="tl-carousel" id="tlCarousel">
        <div class="tl-track" id="tlTrack">
            @forelse($testimonials as $t)
                @php
                    // Build "who" line - position and company
                    $parts = [];
                    if ($t->customer_position) { $parts[] = $t->customer_position; }
                    if ($t->customer_company)  { $parts[] = $t->customer_company;  }
                    $who = implode(' — ', $parts);

                    // Initials from customer name
                    $nameParts = preg_split('/\s+/', trim($t->customer_name));
                    $initials = '';
                    foreach ($nameParts as $p) {
                        if ($p !== '') {
                            $initials .= mb_substr($p, 0, 1);
                        }
                    }

                    // Preview text for card body
                    $preview = Str::limit(strip_tags($t->testimonial_text), 260);
                @endphp

                <div class="tl-slide {{ $loop->first ? 'active' : '' }}">
                    <article
                        class="tl-card"
                        data-fulltext="{{ e($t->testimonial_text) }}"
                        data-name="{{ e($t->customer_name) }}"
                        data-who="{{ e($who) }}"
                    >
                        <div class="initial-badge">{{ $initials }}</div>

                        <blockquote>“{{ $preview }}”</blockquote>

                        <div class="tl-name">{{ $t->customer_name }}</div>

                        @if($who)
                            <div class="tl-role">{{ $who }}</div>
                        @endif
                    </article>
                </div>
            @empty
                <div class="tl-slide active">
                    <article class="tl-card">
                        <blockquote>
                            “No testimonials are available yet. Please check back soon —
                            we’re just getting started.”
                        </blockquote>
                        <div class="tl-name">SharpLync</div>
                        <div class="tl-role">Old school support, modern results.</div>
                    </article>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal for full testimonial --}}
    <div id="testimonialModal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true">
            <button type="button" class="modal-close" aria-label="Close testimonial">&times;</button>

            <p id="modalText" class="modal-text"></p>

            <div class="modal-separator"></div>

            <p id="modalName" class="modal-name"></p>
            <p id="modalRole" class="modal-role"></p>
        </div>
    </div>

</section>

@push('scripts')
<script>
    const track = document.getElementById('testimonialTrack');
    const dots = document.querySelectorAll('#testimonialDots .dot');
    const carousel = document.getElementById('testimonialCarousel');
    let currentIndex = 0;
    let autoSlide;

    function showSlide(index) {
        const slides = document.querySelectorAll('.testimonial-slide');
        if (!slides.length) return;

        currentIndex = (index + slides.length) % slides.length;

        track.style.transform = `translateX(-${currentIndex * 100}%)`;

        slides.forEach((slide, i) =>
            slide.classList.toggle('active', i === currentIndex)
        );
        dots.forEach((dot, i) =>
            dot.classList.toggle('active', i === currentIndex)
        );
    }

    function nextSlide() { showSlide(currentIndex + 1); }
    function startAutoSlide() { autoSlide = setInterval(nextSlide, 6000); }
    function stopAutoSlide() { clearInterval(autoSlide); }

    carousel.addEventListener('mouseenter', stopAutoSlide);
    carousel.addEventListener('mouseleave', startAutoSlide);

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const index = parseInt(dot.getAttribute('data-index'));
            showSlide(index);
        });
    });

    startAutoSlide();
</script>

@endpush
@endsection
