@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/testimonials.css') }}">
@endpush

@section('title', 'SharpLync | Testimonials')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="testimonials-grid-section">
    <div class="container">
        {{-- Page heading --}}
        <div class="testimonials-header">
            <h1>The Foundation of SharpLync: Trusted Expertise</h1>
            <p>Endorsements from industry leaders, colleagues, and partners who vouch for our founder's track record, technical integrity, and commitment to exceptional service.</p>
        </div>

        {{-- Grid Container --}}
        <div class="grid-container">
            @forelse($testimonials as $t)
                @php
                    // Build "who" line - position and company
                    $who = collect([$t->customer_position, $t->customer_company])->filter()->implode(' — ');

                    // Initials from customer name
                    $nameParts = preg_split('/\s+/', trim($t->customer_name));
                    $initials = '';
                    foreach ($nameParts as $p) {
                        if ($p !== '') { $initials .= mb_substr($p, 0, 1); }
                    }

                    // Card content
                    $text = strip_tags($t->testimonial_text);
                    $preview = Str::limit($text, 280, '... <a href="#" class="expand-link" data-open-modal>Read more</a>');
                @endphp

                <article 
                    class="testimonial-card"
                    data-fulltext="{{ e($t->testimonial_text) }}"
                    data-name="{{ e($t->customer_name) }}"
                    data-who="{{ e($who) }}"
                >
                    <div class="initial-badge">{{ $initials }}</div>

                    <blockquote>
                        {{-- Output the preview text; if full text is short, it will display the whole thing --}}
                        <p class="quote-text">{!! $preview !!}</p>
                    </blockquote>

                    <footer class="author-info">
                        <strong class="tl-name">{{ $t->customer_name }}</strong>
                        @if($who)
                            <span class="tl-role">{{ $who }}</span>
                        @endif
                    </footer>
                </article>
            @empty
                <div class="empty-state">
                    <p>No testimonials available yet. Please check back soon.</p>
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
@endsection

@push('scripts')
<script>
// Modal Logic (simplified from your original - )
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('testimonialModal');
    const modalClose = modal.querySelector('.modal-close');
    const modalText = document.getElementById('modalText');
    const modalName = document.getElementById('modalName');
    const modalRole = document.getElementById('modalRole');
    
    // Open Modal
    document.querySelectorAll('[data-open-modal]').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const card = e.target.closest('.testimonial-card');
            if (card) {
                modalText.innerHTML = card.dataset.fulltext.replace(/\n/g, '<br>'); // preserve line breaks
                modalName.textContent = card.dataset.name;
                modalRole.textContent = card.dataset.who;
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close Modal
    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
    
    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });

    // Menu toggle logic (moved from layout)
    window.toggleMenu = function() {
        const overlay = document.getElementById('overlayMenu');
        overlay.classList.toggle('show');
        document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : '';
    }
});
</script>
@endpush