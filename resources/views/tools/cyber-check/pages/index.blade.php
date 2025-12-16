@extends('layouts.base')

@section('title', 'Cybersecurity Check')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tools/cyber-check.css') }}">
@endpush

@section('content')
<div class="tool-wrapper cyber-check">

    <div class="tool-header">
        <h1>Cybersecurity Check</h1>
        <p class="tool-intro">
            A short, plain-English check to help you understand how you’re tracking.
        </p>
    </div>

    <div class="check-selector">
        <button type="button" class="active">Home / Personal</button>
        <button type="button" disabled>Small Business <span>(coming next)</span></button>
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
    @endphp

    <form id="home-check" class="check-form" novalidate>

        <h2>Home Cybersecurity Check</h2>
        <p class="form-intro">Answer honestly — there are no right or wrong answers.</p>

        @foreach ($questions as $index => $q)
            <div class="check-question">
                <p class="question-title">
                    {{ $index + 1 }}. {{ $q['question'] }}
                </p>

                @foreach ($q['options'] as $option)
                    <label>
                        <input
                            type="radio"
                            name="q{{ $index + 1 }}"
                            data-score="{{ $option['score'] }}"
                        >
                        {{ $option['text'] }}
                    </label>
                @endforeach
            </div>
        @endforeach

        <div class="form-actions">
            <button
                type="button"
                id="check-submit"
                class="btn-primary"
                form="home-check"
            >
                See my results
            </button>
        </div>

    </form>

    <div id="check-results" class="check-form hidden">
        <h2>Your Results</h2>
        <p id="result-summary"></p>
        <div class="result-box" id="result-message"></div>

        <div class="form-actions">
            <a href="/about" class="btn-primary">
                Learn more about SharpLync
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tools/cyber-check.js') }}"></script>
@endpush
