@extends('layouts.testimonials-base')
@section('title', 'SharpLync | Testimonials')

@php use Illuminate\Support\Str; @endphp

@section('content')
<section class="testimonials-page">
    <div class="testimonials-title">
        <h1>Customer Testimonials</h1>
        <p>Real words from the people and organisations we’ve supported.</p>
    </div>

    <!-- Dots -->
    <div class="tl-dots" id="tlDots">
        @foreach($testimonials as $index => $t)
            <span class="tl-dot {{ $loop->first ? 'active' : '' }}" data-index="{{ $index }}"></span>
        @endforeach
    </div>

    <!-- Carousel -->
    <div class="tl-carousel" id="tlCarousel">
        <div class="tl-track" id="tlTrack">
            @forelse($testimonials as $t)
                @php
                    $parts = [];
                    if ($t->customer_position) $parts[] = $t->customer_position;
                    if ($t->customer_company)  $parts[] = $t->customer_company;
                    $who = implode(' — ', $parts);

                    $nameParts = preg_split('/\s+/', trim($t->customer_name));
                    $initials = '';
                    foreach ($nameParts as $p) if($p !== '') $initials .= mb_substr($p, 0, 1);

                    $preview = Str::limit(strip_tags($t->testimonial_text), 280);
                @endphp

                <div class="tl-slide {{ $loop->first ? 'active' : '' }}">
                    <article class="tl-card"
                        data-fulltext="{{ e($t->testimonial_text) }}"
                        data-name="{{ e($t->customer_name) }}"
                        data-who="{{ e($who) }}">
                        <div class="initial-badge">{{ $initials }}</div>
                        <blockquote>
                            “{{ $preview }}<span class="read-more">… <a href="#" class="read-more-link">Read full story</a></span>”
                        </blockquote>
                        <div class="tl-name">{{ $t->customer_name }}</div>
                        @if($who)<div class="tl-role">{{ $who }}</div>@endif
                    </article>
                </div>
            @empty
                <div class="tl-slide active">
                    <article class="tl-card">
                        <blockquote>“No testimonials yet — we’re just getting started.”</blockquote>
                        <div class="tl-name">SharpLync</div>
                        <div class="tl-role">Old school support, modern results.</div>
                    </article>
                </div>
            @endforelse

            <!-- Clones for infinite loop (only if >1 testimonial) -->
            @if($testimonials->count() > 1)
                <!-- Clone of first -->
                @php
                    $f = $testimonials->first();
                    $parts = []; if($f->customer_position) $parts[] = $f->customer_position; if($f->customer_company) $parts[] = $f->customer_company; $who = implode(' — ', $parts);
                    $nameParts = preg_split('/\s+/', trim($f->customer_name)); $initials = ''; foreach($nameParts as $p) if($p!=='') $initials .= mb_substr($p,0,1);
                    $preview = Str::limit(strip_tags($f->testimonial_text), 280);
                @endphp
                <div class="tl-slide"><article class="tl-card" data-fulltext="{{ e($f->testimonial_text) }}" data-name="{{ e($f->customer_name) }}" data-who="{{ e($who) }}">
                    <div class="initial-badge">{{ $initials }}</div>
                    <blockquote>“{{ $preview }}<span class="read-more">… <a href="#" class="read-more-link">Read full story</a></span>”</blockquote>
                    <div class="tl-name">{{ $f->customer_name }}</div>
                    @if($who)<div class="tl-role">{{ $who }}</div>@endif
                </article></div>

                <!-- Clone of last -->
                @php
                    $l = $testimonials->last();
                    $parts = []; if($l->customer_position) $parts[] = $l->customer_position; if($l->customer_company) $parts[] = $l->customer_company; $who = implode(' — ', $parts);
                    $nameParts = preg_split('/\s+/', trim($l->customer_name)); $initials = ''; foreach($nameParts as $p) if($p!=='') $initials .= mb_substr($p,0,1);
                    $preview = Str::limit(strip_tags($l->testimonial_text), 280);
                @endphp
                <div class="tl-slide"><article class="tl-card" data-fulltext="{{ e($l->testimonial_text) }}" data-name="{{ e($l->customer_name) }}" data-who="{{ e($who) }}">
                    <div class="initial-badge">{{ $initials }}</div>
                    <blockquote>“{{ $preview }}<span class="read-more">… <a href="#" class="read-more-link">Read full story</a></span>”</blockquote>
                    <div class="tl-name">{{ $l->customer_name }}</div>
                    @if($who)<div class="tl-role">{{ $who }}</div>@endif
                </article></div>
            @endif
        </div>
    </div>

    <!-- Full Testimonial Modal -->
    <div id="testimonialModal" class="modal-backdrop">
        <div class="modal-dialog">
            <button type="button" class="modal-close" aria-label="Close">×</button>
            <p id="modalText" class="modal-text"></p>
            <div class="modal-name" id="modalName"></div>
            <div class="modal-role" id="modalRole"></div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const track   = document.querySelector(".tl-track");
    const slides  = document.querySelectorAll(".tl-slide");
    const dots    = document.querySelectorAll(".tl-dot");
    const modal   = document.getElementById("testimonialModal");
    const modalText = document.getElementById("modalText");
    const modalName = document.getElementById("modalName");
    const modalRole = document.getElementById("modalRole");

    const realCount = dots.length;
    let current = 0;
    let slideWidth = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    let isDragging = false;
    let startX = 0;
    let autoTimer;

    function updateSlideWidth() {
        if (!slides.length) return;
        const s = slides[0];
        const style = getComputedStyle(s);
        slideWidth = s.offsetWidth + parseFloat(style.marginLeft||0) + parseFloat(style.marginRight||0) + 30;
    }

    function setPosition() {
        track.style.transition = "transform 0.7s cubic-bezier(0.4, 0, 0.2, 1)";
        track.style.transform = `translateX(${currentTranslate}px)`;
    }

    function goTo(index) {
        current = index;
        const visual = current + 1; // +1 because of leading clone
        currentTranslate = -visual * slideWidth;
        prevTranslate = currentTranslate;
        setPosition();

        slides.forEach((s,i) => s.classList.toggle("active", i === visual));
        dots.forEach((d,i) => d.classList.toggle("active", i === current));
    }

    function checkLoop() {
        const visual = current + 1;
        if (visual >= realCount + 1) {
            current = 0;
            track.style.transition = "none";
            currentTranslate = -slideWidth;
            setPosition();
            requestAnimationFrame(() => { track.style.transition = "transform 0.7s cubic-bezier(0.4, 0, 0.2, 1)"; goTo(0); });
        }
        if (visual < 1) {
            current = realCount - 1;
            track.style.transition = "none";
            currentTranslate = -(realCount * slideWidth);
            setPosition();
            requestAnimationFrame(() => { track.style.transition = "transform 0.7s cubic-bezier(0.4, 0, 0.2, 1)"; goTo(current); });
        }
    }

    function next() { current++; goTo(current); checkLoop(); }
    function prev() { current--; goTo(current); checkLoop(); }

    function startAuto() { stopAuto(); autoTimer = setInterval(next, 7000); }
    function stopAuto() { clearInterval(autoTimer); }

    // Drag/Touch
    function dragStart(e) { stopAuto(); isDragging = true; startX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX; prevTranslate = currentTranslate; }
    function dragMove(e) { if(isDragging) { const x = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX; currentTranslate = prevTranslate + (x - startX); track.style.transition = "none"; track.style.transform = `translateX(${currentTranslate}px)`; }}
    function dragEnd() {
        if(!isDragging) return; isDragging = false;
        const moved = currentTranslate - prevTranslate;
        if(Math.abs(moved) > 50) moved < 0 ? next() : prev(); else goTo(current);
        startAuto();
    }

    slides.forEach(s => {
        s.addEventListener("dragstart", e=>e.preventDefault());
        s.addEventListener("mousedown", dragStart);
        s.addEventListener("mousemove", dragMove);
        s.addEventListener("mouseup", dragEnd);
        s.addEventListener("mouseleave", dragEnd);
        s.addEventListener("touchstart", dragStart);
        s.addEventListener("touchmove", dragMove);
        s.addEventListener("touchend", dragEnd);
    });

    // Dots
    dots.forEach((d,i) => d.addEventListener("click", () => { current = i; goTo(current); startAuto(); }));

    // Modal: open on card click
    document.querySelectorAll('.tl-card').forEach(card => {
        card.style.cursor = "pointer";
        card.addEventListener('click', e => {
            if(e.target.closest('.read-more-link')) return;
            modalText.textContent = card.dataset.fulltext;
            modalName.textContent = card.dataset.name;
            modalRole.textContent = card.dataset.who || '';
            modal.classList.add('open');
        });
    });

    // Close modal
    document.querySelector('.modal-close').addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', e => { if(e.target === modal) modal.classList.remove('open'); });

    // Init
    updateSlideWidth();
    currentTranslate = -slideWidth;
    prevTranslate = currentTranslate;
    setPosition();
    goTo(0);
    startAuto();

    window.addEventListener("resize", () => { updateSlideWidth(); goTo(current); });
});
</script>
@endpush
@endsection