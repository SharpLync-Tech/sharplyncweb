{{-- 
|--------------------------------------------------------------------------
| Cyber Glossary – Index
| Version: v1.0
|--------------------------------------------------------------------------
--}}

@extends('tools.cyber-glossary.layouts.glossary-layout')

@section('tool-content')

<div class="glossary-grid">

    @include('tools.cyber-glossary.partials.term-card', [
    'term' => 'Ransomware',
    'summary' => 'Malicious software that locks your files and demands payment.',
    'explanation' => 'Ransomware encrypts your files or systems so you can’t access them unless a ransom is paid. Your data is still there, but it’s been locked using encryption that only the attacker controls.',
    'analogy' => 'It’s like going away for the weekend and coming home to find the locks changed and a note demanding money for the keys. You might get a locksmith to let you back inside, or even break a window, but once you’re in, all the cupboards, drawers, and doors inside are still locked. That’s what ransomware does, your data is still there, but it’s locked and unusable.',
    'why' => 'Even if you pay, there’s no guarantee you’ll get your data back.'
    ])


    @include('tools.cyber-glossary.partials.term-card', [
    'term' => 'Malware',
    'summary' => 'A broad term for software designed to harm, spy on, or interfere with your device.',
    'explanation' => 'Malware is unwanted software that installs itself without your permission. It can spy on what you do, steal information, slow your device down, or open the door for further attacks.',
    'analogy' => 'It’s like someone sneaking into your house while you’re not home. They don’t smash anything or make a mess — they quietly copy your documents, watch what you do, and leave doors unlocked so they can come back later.',
    'why' => 'Because malware often runs silently, it can cause damage or steal information for weeks or months before anyone notices.'
    ])

    @include('tools.cyber-glossary.partials.term-card', [
    'term' => 'Phishing',
    'summary' => 'Scam messages that pretend to be from people or organisations you trust.',
    'explanation' => 'Phishing messages are designed to trick you into clicking links, opening attachments, or giving away information like passwords or payment details.',
    'analogy' => 'It’s like someone wearing an AusPost uniform knocking on your door and saying there’s a problem with a delivery. They ask you to unlock the door, show ID, or sign something — and the moment you do, you’ve let them in.',
    'why' => 'Phishing works because it relies on trust and urgency. One convincing message is often all it takes to start a larger attack.'
    ])

</div>

<div class="glossary-footer-cta">
    <p>Want to know how you’re tracking overall?</p>
    <a href="/tools/cyber-check" class="btn-primary">
        Take the Cybersecurity Check
    </a>
</div>

@endsection

<script>
document.addEventListener('click', function (e) {
    const button = e.target.closest('.term-toggle');
    if (!button) return;

    const card = button.closest('.glossary-card');
    const details = card.querySelector('.term-details');
    const isOpen = button.getAttribute('aria-expanded') === 'true';

    // Close all cards first
    document.querySelectorAll('.glossary-card').forEach(c => {
        c.classList.remove('active');
        c.querySelector('.term-details').hidden = true;
        const btn = c.querySelector('.term-toggle');
        btn.setAttribute('aria-expanded', 'false');
        btn.textContent = 'Read more';
    });

    // If it was closed, open it
    if (!isOpen) {
        card.classList.add('active');
        details.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        button.textContent = 'Show less';
    }
});
</script>

