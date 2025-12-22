@extends('layouts.testimonials-base') 

@section('title', 'SharpLync | Testimonials')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="testimonials-grid-section">
    <div class="container">
        {{-- Page heading --}}
        <div class="testimonials-header">
            <h1>
                The Foundation of SharpLync: <span class="gradient">Trusted Expertise</span>
            </h1>
            <p>
                Endorsements from industry leaders, colleagues, and partners who vouch for our founder's
                track record, technical integrity, and commitment to exceptional service.
            </p>
        </div>

        {{-- 🔎 DEBUG PANEL — REMOVE AFTER FIX --}}
        <div style="
            background:#0A2A4D;
            color:#ffffff;
            padding:14px;
            margin:20px 0;
            border-radius:8px;
            font-size:14px;
        ">
            <strong>Testimonials Debug</strong><br><br>

            Variable exists:
            <strong>{{ isset($testimonials) ? 'YES' : 'NO' }}</strong><br>

            Variable type:
            <strong>
                {{ isset($testimonials) ? get_class($testimonials) : 'N/A' }}
            </strong><br>

            Count:
            <strong>
                {{ isset($testimonials) ? $testimonials->count() : 'N/A' }}
            </strong><br>

            @if(isset($testimonials) && $testimonials->count())
                IDs:
                <strong>{{ $testimonials->pluck('id')->implode(', ') }}</strong>
            @endif
        </div>
        {{-- 🔎 END DEBUG PANEL --}}
            {{-- 🔥 HARD DEBUG — REMOVE AFTER --}}
            <div style="background:#111; color:#0f0; padding:15px; margin-bottom:25px; font-family:monospace; font-size:13px;">
                <strong>TESTIMONIALS HARD DEBUG</strong><br><br>

                <strong>Model</strong><br>
                Class: {{ $debug['model']['model_class'] ?? 'n/a' }}<br>
                Connection: {{ $debug['model']['connection_name'] ?? 'n/a' }}<br>
                Table: {{ $debug['model']['table'] ?? 'n/a' }}<br><br>

                <strong>Database</strong><br>
                Status: {{ $debug['db']['status'] ?? 'n/a' }}<br>
                Driver: {{ $debug['db']['driver'] ?? 'n/a' }}<br>
                Database: {{ $debug['db']['database'] ?? 'n/a' }}<br><br>

                <strong>Counts</strong><br>
                Raw table count: {{ $debug['raw_table_count'] }}<br>
                Eloquent filtered count: {{ $debug['eloquent_count'] }}<br>
            </div>

        {{-- Grid Container --}}
        <div class="grid-container">
            @forelse($testimonials as $t)
                @php
                    // Build "who" line - position and company
                    $who = collect([$t->customer_position, $t->customer_company])
                        ->filter()
                        ->implode(' — ');

                    // Initials from customer name
                    $nameParts = preg_split('/\s+/', trim($t->customer_name));
                    $initials = '';
                    foreach ($nameParts as $p) {
                        if ($p !== '') {
                            $initials .= mb_substr($p, 0, 1);
                        }
                    }

                    // Card content
                    $text = strip_tags($t->testimonial_text);
                    $preview = Str::limit(
                        $text,
                        280,
                        '... <a href="#" class="expand-link" data-open-modal>Read more</a>'
                    );
                @endphp

                <article 
                    class="testimonial-card"
                    data-fulltext="{{ e($t->testimonial_text) }}"
                    data-name="{{ e($t->customer_name) }}"
                    data-who="{{ e($who) }}"
                >
                    <div class="initial-badge">{{ $initials }}</div>

                    <blockquote>
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

            <div class="modal-top-bar"></div>

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
document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('testimonialModal');
    const modalClose = modal.querySelector('.modal-close');
    const modalText = document.getElementById('modalText');
    const modalName = document.getElementById('modalName');
    const modalRole = document.getElementById('modalRole');

    document.querySelectorAll('[data-open-modal]').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();

            const card = e.target.closest('.testimonial-card');
            if (!card) return;

            modalText.innerHTML = card.dataset.fulltext.replace(/\n/g, '<br>');
            modalName.textContent = card.dataset.name;
            modalRole.textContent = card.dataset.who;

            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    modalClose.addEventListener('click', closeModal);

    modal.addEventListener('click', e => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });

    window.toggleMenu = function() {
        const overlay = document.getElementById('overlayMenu');
        overlay.classList.toggle('show');
        document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : '';
    }
});
</script>
@endpush
