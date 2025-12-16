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
        <button type="button" class="active" data-check="home">
            Home / Personal
        </button>

        <button type="button" data-check="business" disabled>
            Small Business <span>(coming next)</span>
        </button>
    </div>

    <!-- Home Assessment -->
    <form id="home-check" class="check-form" method="POST" action="#">
        @csrf

        <h2>Home Cybersecurity Check</h2>
        <p class="form-intro">
            Answer honestly — there are no right or wrong answers.
        </p>

        <!-- Question 1 -->
        <div class="check-question">
            <p class="question-title">1. How do you protect your online accounts?</p>
            <label><input type="radio" name="q1"> I reuse the same password</label>
            <label><input type="radio" name="q1"> I use different passwords</label>
            <label><input type="radio" name="q1"> I use a password manager and extra sign-in checks</label>
        </div>

        <!-- Question 2 -->
        <div class="check-question">
            <p class="question-title">2. Do your devices update automatically?</p>
            <label><input type="radio" name="q2"> I’m not sure</label>
            <label><input type="radio" name="q2"> I update them sometimes</label>
            <label><input type="radio" name="q2"> Updates happen automatically</label>
        </div>

        <!-- Question 3 -->
        <div class="check-question">
            <p class="question-title">
                3. If your computer or phone was lost today, what would happen to your files?
            </p>
            <label><input type="radio" name="q3"> I’d lose most of them</label>
            <label><input type="radio" name="q3"> Some are backed up</label>
            <label><input type="radio" name="q3"> Everything important is backed up</label>
        </div>

        <!-- Question 4 -->
        <div class="check-question">
            <p class="question-title">
                4. How confident are you at spotting scam messages?
            </p>
            <label><input type="radio" name="q4"> Not confident</label>
            <label><input type="radio" name="q4"> Somewhat confident</label>
            <label><input type="radio" name="q4"> Very confident and cautious</label>
        </div>

        <!-- Question 5 -->
        <div class="check-question">
            <p class="question-title">
                5. Do you use extra sign-in checks for important accounts like email or banking?
            </p>
            <label><input type="radio" name="q5"> No</label>
            <label><input type="radio" name="q5"> Some accounts</label>
            <label><input type="radio" name="q5"> All important accounts</label>
        </div>

        <!-- Submit -->
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                See my results
            </button>
        </div>

    </form>

</div>
@endsection
