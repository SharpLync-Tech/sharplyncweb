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
            <label><input type="radio" name="q1" data-score="0">I reuse the same password</label>
            <label><input type="radio" name="q1" data-score="1">I use different passwords</label>
            <label><input type="radio" name="q1" data-score="2">I use a password manager and extra sign-in checks</label>

        </div>

        <!-- Question 2 -->
        <div class="check-question">
            <p class="question-title">2. Do your devices update automatically?</p>
            <label><input type="radio" name="q2" data-score="0"> I’m not sure</label>
            <label><input type="radio" name="q2" data-score="1"> I update them sometimes</label>
            <label><input type="radio" name="q2" data-score="2"> Updates happen automatically</label>
        </div>

        <!-- Question 3 -->
        <div class="check-question">
            <p class="question-title">
                3. If your computer or phone was lost today, what would happen to your files?
            </p>
            <label><input type="radio" name="q3" data-score="0"> I’d lose most of them</label>
            <label><input type="radio" name="q3" data-score="1"> Some are backed up</label>
            <label><input type="radio" name="q3" data-score="2"> Everything important is backed up</label>
        </div>

        <!-- Question 4 -->
        <div class="check-question">
            <p class="question-title">
                4. How confident are you at spotting scam messages?
            </p>
            <label><input type="radio" name="q4" data-score="0"> Not confident</label>
            <label><input type="radio" name="q4" data-score="1"> Somewhat confident</label>
            <label><input type="radio" name="q4" data-score="2"> Very confident and cautious</label>
        </div>

        <!-- Question 5 -->
        <div class="check-question">
            <p class="question-title">
                5. Do you use extra sign-in checks for important accounts like email or banking?
            </p>
            <label><input type="radio" name="q5" data-score="0"> No</label>
            <label><input type="radio" name="q5" data-score="1"> Some accounts</label>
            <label><input type="radio" name="q5" data-score="2"> All important accounts</label>
        </div>

        <!-- Submit -->
        <div class="form-actions">
            <button type="submit" class="btn-primary">
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

<script>
document.getElementById('home-check').addEventListener('submit', function (e) {
    e.preventDefault();

    let totalScore = 0;
    let totalQuestions = 5;

    for (let i = 1; i <= totalQuestions; i++) {
        const selected = document.querySelector(`input[name="q${i}"]:checked`);
        if (!selected) {
            alert('Please answer all questions before seeing your results.');
            return;
        }
        totalScore += parseInt(selected.dataset.score);
    }

    // Hide form
    this.classList.add('hidden');

    // Show results
    const results = document.getElementById('check-results');
    const summary = document.getElementById('result-summary');
    const message = document.getElementById('result-message');

    let headline = '';
    let explanation = '';

    if (totalScore <= 4) {
        headline = 'Your cybersecurity needs attention';
        explanation = 'There are a few gaps that could put your data at risk. The good news is that most of these are easy to improve with the right setup.';
    } else if (totalScore <= 7) {
        headline = 'You’re doing okay, but there’s room to improve';
        explanation = 'You’ve got some good habits in place, but tightening a few areas would make a big difference.';
    } else {
        headline = 'You’re in good shape';
        explanation = 'You’re doing many of the right things already. A quick review could help make sure nothing is being missed.';
    }

    summary.innerHTML = `<strong>Score:</strong> ${totalScore} / 10`;
    message.innerHTML = `<strong>${headline}</strong><br>${explanation}`;

    results.classList.remove('hidden');
    results.scrollIntoView({ behavior: 'smooth' });
});
</script>

