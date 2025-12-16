@extends('layouts.base')

@section('title', 'Cybersecurity Check')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tools/cyber-check.css') }}">
@endpush

@section('content')
<div class="tool-wrapper cyber-check">

    <!-- Header -->
    <div class="tool-header">
        <h1>Cybersecurity Check</h1>
        <p class="tool-intro">
            A short, plain-English check to help you understand how you’re tracking.
        </p>
    </div>

    <!-- Selector -->
    <div class="check-selector">
        <button type="button" data-check="home">Home / Personal</button>
        <button type="button" data-check="business">Small Business</button>
    </div>

    @php
        $questions = [
            [
                'question' => 'How do you protect your online accounts?',
                'options' => [
                    ['text' => 'I reuse the same password', 'score' => 0],
                    ['text' => 'I use different passwords', 'score' => 1],
                    ['text' => 'I use a password manager and extra sign-in checks', 'score' => 2],
                ],
            ],
            [
                'question' => 'Do your devices update automatically?',
                'options' => [
                    ['text' => 'I’m not sure', 'score' => 0],
                    ['text' => 'I update them sometimes', 'score' => 1],
                    ['text' => 'Updates happen automatically', 'score' => 2],
                ],
            ],
            [
                'question' => 'If your device was lost today, what would happen to your files?',
                'options' => [
                    ['text' => 'I’d lose most of them', 'score' => 0],
                    ['text' => 'Some are backed up', 'score' => 1],
                    ['text' => 'Everything important is backed up', 'score' => 2],
                ],
            ],
            [
                'question' => 'How confident are you at spotting scam messages?',
                'options' => [
                    ['text' => 'Not confident', 'score' => 0],
                    ['text' => 'Somewhat confident', 'score' => 1],
                    ['text' => 'Very confident and cautious', 'score' => 2],
                ],
            ],
            [
                'question' => 'Do you use extra sign-in checks for important accounts?',
                'options' => [
                    ['text' => 'No', 'score' => 0],
                    ['text' => 'Some accounts', 'score' => 1],
                    ['text' => 'All important accounts', 'score' => 2],
                ],
            ],
        ];

        $businessQuestions = [
            [
                'question' => 'How do staff sign in to work accounts?',
                'options' => [
                    ['text' => 'The same password is reused', 'score' => 0],
                    ['text' => 'Different passwords, no extra checks', 'score' => 1],
                    ['text' => 'Password manager and extra sign-in checks', 'score' => 2],
                ],
            ],
            [
                'question' => 'How are computers and systems kept up to date?',
                'options' => [
                    ['text' => 'Updates are manual or often missed', 'score' => 0],
                    ['text' => 'Updates happen sometimes', 'score' => 1],
                    ['text' => 'Updates install automatically', 'score' => 2],
                ],
            ],
            [
                'question' => 'If a staff laptop was lost today, what happens to company data?',
                'options' => [
                    ['text' => 'Data would be lost or exposed', 'score' => 0],
                    ['text' => 'Some data could be recovered', 'score' => 1],
                    ['text' => 'Everything important is backed up securely', 'score' => 2],
                ],
            ],
            [
                'question' => 'How confident are staff at spotting fake emails or messages?',
                'options' => [
                    ['text' => 'Not confident', 'score' => 0],
                    ['text' => 'Some awareness or informal training', 'score' => 1],
                    ['text' => 'Regular training and strong awareness', 'score' => 2],
                ],
            ],
            [
                'question' => 'Do you know who has access to company systems and data?',
                'options' => [
                    ['text' => 'Access is unmanaged or unclear', 'score' => 0],
                    ['text' => 'Some access control, not reviewed often', 'score' => 1],
                    ['text' => 'Access is controlled and reviewed regularly', 'score' => 2],
                ],
            ],
        ];
    @endphp

    <!-- Business Check (starts hidden) -->
    <form id="business-check" class="check-form hidden" novalidate>
        <h2>Small Business Cybersecurity Check</h2>
        <p class="form-intro">Answer honestly — this helps highlight practical areas to improve.</p>

        @foreach ($businessQuestions as $index => $q)
            <div class="check-question">
                <p class="question-title">{{ $index + 1 }}. {{ $q['question'] }}</p>

                @foreach ($q['options'] as $option)
                    <label>
                        <input type="radio" name="bq{{ $index + 1 }}" data-score="{{ $option['score'] }}">
                        {{ $option['text'] }}
                    </label>
                @endforeach
            </div>
        @endforeach

        <div class="form-actions">
            <button type="button" id="business-submit" class="btn-primary">
                See my results
            </button>
        </div>
    </form>

    <!-- Home Check (starts hidden) -->
    <form id="home-check" class="check-form hidden" novalidate>
        <h2>Home Cybersecurity Check</h2>
        <p class="form-intro">Answer honestly — there are no right or wrong answers.</p>

        @foreach ($questions as $index => $q)
            <div class="check-question">
                <p class="question-title">{{ $index + 1 }}. {{ $q['question'] }}</p>

                @foreach ($q['options'] as $option)
                    <label>
                        <input type="radio" name="q{{ $index + 1 }}" data-score="{{ $option['score'] }}">
                        {{ $option['text'] }}
                    </label>
                @endforeach
            </div>
        @endforeach

        <div class="form-actions">
            <button type="button" id="check-submit" class="btn-primary">
                See my results
            </button>
        </div>
    </form>

    <!-- Results (starts hidden) -->
    <div id="check-results" class="check-form hidden">
        <h2>Your Results</h2>
        <p id="result-summary"></p>
        <div class="result-box" id="result-message"></div>

        <div class="form-actions">
            <a href="/about" class="btn-primary">Learn more about SharpLync</a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tools/cyber-check.js') }}"></script>
@endpush