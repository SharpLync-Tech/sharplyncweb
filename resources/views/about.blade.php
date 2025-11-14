@extends('layouts.content')

@section('title', 'SharpLync | About')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')

<section class="about-page">

    <h1>About <span class="highlight">SharpLync</span></h1>
    <p style="text-align:center; max-width: 700px; margin: 0 auto 2rem;">
        From the Granite Belt to the Cloud, bridging the gap between people and technology with old school support and modern results.
    </p>

    <!-- STORY CARD -->
    <div class="about-card">
        <h3>My Story: From Tools to Technology</h3>

        <div id="storyIntro">
            <p>My journey into technology didn’t start in a lab or an office. It started with a set of tools, cables, and a good dose of curiosity.</p>

            <p>I began my career as an Electrical Fitter, learning the value of precision, safety, and doing things properly the first time. From there, my interest naturally shifted toward the growing world of data and communication...</p>
        </div>

        <div id="storyFull" class="collapsed">
            <p>That experience showed me how much people appreciate honest, down-to-earth support...</p>

            <p>In the early 2000s, I took a leap and started my own business...</p>

            <h3>Establishing Expertise at Scale</h3>
            <p>During my time working for a large school network...</p>

            <h3>The Launch of SharpLync</h3>
            <p>After more than a decade managing complex infrastructure...</p>

            <p>I believe in old school support with modern results...</p>
        </div>

        <button id="toggleStory" class="toggle-btn">Continue My Story +</button>
    </div>

    <!-- TESTIMONIALS -->
    <section class="testimonials-section">
        <h3>What People Say</h3>

        <div class="testimonial-wrapper">
            <div class="testimonial-container">
                @foreach ($testimonials as $index => $t)
                    @php
                        $who = trim(($t->customer_position ?? '') . 
                                    (($t->customer_position && $t->customer_company) ? ' — ' : '') .
                                     ($t->customer_company ?? ''));

                        $preview = Str::limit(strip_tags($t->testimonial_text), 300);
                    @endphp

                    <div class="testimonial {{ $index === 0 ? 'active' : '' }}"
                         data-fulltext="{{ e($t->testimonial_text) }}"
                         data-name="{{ e($t->customer_name) }}"
                         data-who="{{ e($who) }}">
                         
                        <div class="testimonial-meta">
                            <h4>{{ $t->customer_name }}</h4>
                            @if ($who)
                                <p class="testimonial-role">{{ $who }}</p>
                            @endif
                        </div>

                        <p class="testimonial-preview">"{{ $preview }}"</p>

                        <button class="testimonial-read-more">Read more…</button>
                    </div>

                @endforeach
            </div>

            <div class="testimonial-dots"></div>
        </div>
    </section>

</section>

<!-- WOW MODAL -->
<div id="testimonialModal" class="testimonial-modal">
    <div class="testimonial-modal-backdrop"></div>

    <div class="testimonial-modal-dialog">
        <button class="testimonial-modal-close">×</button>

        <div class="testimonial-modal-quote">“</div>

        <p id="modalText" class="testimonial-modal-text"></p>

        <div class="testimonial-modal-separator"></div>

        <p id="modalName" class="testimonial-modal-name"></p>
        <p id="modalRole" class="testimonial-modal-role"></p>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // STORY EXPAND
    const toggleBtn = document.getElementById('toggleStory');
    const fullStory = document.getElementById('storyFull');

    toggleBtn.addEventListener('click', () => {
        fullStory.classList.toggle('collapsed');
        toggleBtn.textContent = fullStory.classList.contains('collapsed')
            ? 'Continue My Story +'
            : 'Show Less –';
    });

    // SLIDER
    const testimonials = document.querySelectorAll('.testimonial');
    const dotsContainer = document.querySelector('.testimonial-dots');

    let index = 0;
    let interval = null;

    function goTo(i) {
        index = i;
        testimonials.forEach((t, idx) => t.classList.toggle('active', idx === index));
        document.querySelectorAll('.testimonial-dot')
            .forEach((d, idx) => d.classList.toggle('active', idx === index));
    }

    function buildDots() {
        testimonials.forEach((_, i) => {
            const d = document.createElement('button');
            d.className = 'testimonial-dot' + (i === 0 ? ' active' : '');
            d.addEventListener('click', () => {
                stop();
                goTo(i);
                start();
            });
            dotsContainer.appendChild(d);
        });
    }

    function start() {
        interval = setInterval(() => {
            goTo((index + 1) % testimonials.length);
        }, 15000);
    }

    function stop() {
        clearInterval(interval);
    }

    buildDots();
    start();

    // MODAL
    const modal = document.getElementById('testimonialModal');
    const modalText = document.getElementById('modalText');
    const modalName = document.getElementById('modalName');
    const modalRole = document.getElementById('modalRole');

    const readMoreBtns = document.querySelectorAll('.testimonial-read-more');
    readMoreBtns.forEach(btn => {
        btn.addEventListener('click', e => {
            const card = e.target.closest('.testimonial');
            modalText.textContent = card.dataset.fulltext;
            modalName.textContent = card.dataset.name;
            modalRole.textContent = card.dataset.who;

            modal.classList.add('open');
            stop();
        });
    });

    document.querySelector('.testimonial-modal-close').addEventListener('click', () => {
        modal.classList.remove('open');
        start();
    });

    modal.querySelector('.testimonial-modal-backdrop').addEventListener('click', () => {
        modal.classList.remove('open');
        start();
    });

});
</script>
@endpush
