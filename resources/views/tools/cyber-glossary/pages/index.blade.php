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
    'analogy' => 'It’s like going away for the weekend and coming home to find the locks changed and a note demanding money for the keys. You might get a locksmith to let you back inside — or even break a window — but once you’re in, all the cupboards, drawers, and doors inside are still locked. That’s what ransomware does — your data is still there, but it’s locked and unusable.',
    'why' => 'Even if you pay, there’s no guarantee you’ll get your data back.'
    ])


    @include('tools.cyber-glossary.partials.term-card', [
        'term' => 'Malware',
        'summary' => 'A broad term for software designed to harm or spy on devices.',
        'explanation' => 'Malware includes viruses, spyware, and other unwanted software that runs without your permission.',
        'analogy' => 'It’s like someone sneaking into your house and quietly copying your documents.',
        'why' => 'Malware can steal data long before you realise anything is wrong.'
    ])

    @include('tools.cyber-glossary.partials.term-card', [
        'term' => 'Phishing',
        'summary' => 'Scam messages pretending to be from trusted sources.',
        'explanation' => 'Phishing attempts trick users into clicking links or sharing information.',
        'analogy' => 'It’s like someone wearing an AusPost uniform who isn’t actually from AusPost.',
        'why' => 'Most cyber attacks start with a single convincing email.'
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

