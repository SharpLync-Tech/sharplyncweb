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
document.addEventListener('DOMContentLoaded', () => {
    // ===== Carousel basics =====
    const track   = document.getElementById('tlTrack');
    const slides  = Array.from(document.querySelectorAll('.tl-slide'));
    const dots    = Array.from(document.querySelectorAll('#tlDots .tl-dot'));
    const carousel = document.getElementById('tlCarousel');

    let currentIndex = 0;
    let autoTimer = null;

    function goTo(index) {
        if (!slides.length) return;

        const count = slides.length;
        currentIndex = (index + count) % count;

        // Move track
        track.style.transform = `translateX(-${currentIndex * 100}%)`;

        // Active slide scale
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === currentIndex);
        });

        // Active dot
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentIndex);
        });
    }

    function nextSlide() {
        goTo(currentIndex + 1);
    }

    function startAuto() {
        if (autoTimer || slides.length <= 1) return;
        autoTimer = setInterval(nextSlide, 16000); // ~16s per testimonial
    }

    function stopAuto() {
        if (!autoTimer) return;
        clearInterval(autoTimer);
        autoTimer = null;
    }

    // Dot click
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            stopAuto();
            goTo(i);
            startAuto();
        });
    });

    // Pause on hover (desktop)
    if (carousel) {
        carousel.addEventListener('mouseenter', stopAuto);
        carousel.addEventListener('mouseleave', startAuto);
    }

    // Init
    goTo(0);
    startAuto();

    // ===== Modal logic =====
    const modal      = document.getElementById('testimonialModal');
    const modalText  = document.getElementById('modalText');
    const modalName  = document.getElementById('modalName');
    const modalRole  = document.getElementById('modalRole');
    const closeBtn   = modal ? modal.querySelector('.modal-close') : null;

    function openModal(fromCard) {
        if (!modal || !fromCard) return;

        const fullText = fromCard.dataset.fulltext || '';
        const name     = fromCard.dataset.name || '';
        const who      = fromCard.dataset.who || '';

        // Clean leading quote if present
        const cleaned = fullText.replace(/^["“”]/, '');

        modalText.textContent = cleaned;
        modalName.textContent = name;

        if (who) {
            modalRole.textContent = who;
            modalRole.style.display = 'block';
        } else {
            modalRole.textContent = '';
            modalRole.style.display = 'none';
        }

        stopAuto();
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        startAuto();
    }

    // Clicking card opens modal
    slides.forEach(slide => {
        const card = slide.querySelector('.tl-card');
        if (!card) return;

        card.addEventListener('click', () => openModal(card));
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Close on Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains('open')) {
            closeModal();
        }
    });

    // Close when clicking outside dialog
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
});
</script>
@endpush
@endsection
