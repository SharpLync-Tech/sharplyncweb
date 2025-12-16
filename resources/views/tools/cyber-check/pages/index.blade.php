@extends('layouts.base')
<meta name="csrf-token" content="{{ csrf_token() }}">

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

    <form id="home-check" class="check-form" novalidate>

        <h2>Home Cybersecurity Check</h2>
        <p class="form-intro">Answer honestly — there are no right or wrong answers.</p>

        @for ($i = 1; $i <= 5; $i++)
            <div class="check-question">
                <p class="question-title">
                    {{ $i }}.
                    @switch($i)
                        @case(1) How do you protect your online accounts? @break
                        @case(2) Do your devices update automatically? @break
                        @case(3) If your device was lost today, what would happen to your files? @break
                        @case(4) How confident are you at spotting scam messages? @break
                        @case(5) Do you use extra sign-in checks for important accounts? @break
                    @endswitch
                </p>

                <label><input type="radio" name="q{{ $i }}" data-score="0"> Option 1</label>
                <label><input type="radio" name="q{{ $i }}" data-score="1"> Option 2</label>
                <label><input type="radio" name="q{{ $i }}" data-score="2"> Option 3</label>
            </div>
        @endfor

        <div class="form-actions">
            <button
    type="button"
    id="check-submit"
    class="btn-primary"
    form="home-check"
>

        </div>

    </form>

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
