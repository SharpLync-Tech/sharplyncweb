{{-- 
|--------------------------------------------------------------------------
| Cyber Glossary Term Card
| Version: v1.0
|--------------------------------------------------------------------------
| Props:
| - $term
| - $summary
| - $explanation
| - $analogy (optional)
| - $why (optional)
|--------------------------------------------------------------------------
--}}

<div class="glossary-card">
    <h3>{{ $term }}</h3>

    <p class="term-summary">
        {{ $summary }}
    </p>

    <button class="term-toggle" type="button" aria-expanded="false">
        Read more
    </button>

    <div class="term-details" hidden>
        <p class="term-explanation">
            {{ $explanation }}
        </p>

        @isset($analogy)
            <p class="term-analogy">
                <strong>Think of it like this:</strong><br>
                {{ $analogy }}
            </p>
        @endisset

        @isset($why)
            <p class="term-why">
                <strong>Why it matters:</strong><br>
                {{ $why }}
            </p>
        @endisset
    </div>
</div>