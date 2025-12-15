{{-- 
|--------------------------------------------------------------------------
| Cyber Glossary – Index
| Version: v1.0
|--------------------------------------------------------------------------
--}}

@extends('tools.cyber-glossary.layouts.glossary-layout')

@section('tool-content')

<div class="glossary-grid">

    {{-- Placeholder cards – real content comes next --}}
    <div class="glossary-card placeholder">
        <h3>Ransomware</h3>
        <p>Plain-English explanation coming soon.</p>
    </div>

    <div class="glossary-card placeholder">
        <h3>Malware</h3>
        <p>Plain-English explanation coming soon.</p>
    </div>

    <div class="glossary-card placeholder">
        <h3>Phishing</h3>
        <p>Plain-English explanation coming soon.</p>
    </div>

    <div class="glossary-card placeholder">
        <h3>Multi-Factor Authentication (MFA)</h3>
        <p>Plain-English explanation coming soon.</p>
    </div>

    <div class="glossary-card placeholder">
        <h3>Backups</h3>
        <p>Plain-English explanation coming soon.</p>
    </div>

</div>

<div class="glossary-footer-cta">
    <p>Want to know how you’re tracking overall?</p>
    <a href="/tools/cyber-check" class="btn-primary">
        Take the Cybersecurity Check
    </a>
</div>

@endsection
