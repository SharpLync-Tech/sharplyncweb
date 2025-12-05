@extends('layouts.base')

@section('title', 'Policies | SharpLync')

@section('content')

<link rel="stylesheet" href="{{ asset('css/policies.css') }}">

<div class="policy-wrapper" style="max-width: 1000px; margin: 3rem auto;">
    <div class="policy-container">

        <h1 class="policy-title">Policies</h1>
        <div class="policy-version">Central hub for all SharpLync legal & operational policies</div>
        <div class="policy-updated" style="margin-bottom: 3rem;">
            Updated automatically as we release new documentation
        </div>

        <div class="policy-grid">

            <!-- TERMS -->
            <a href="/policies/terms" class="policy-card">
                <h3>Terms & Conditions</h3>
                <p>Service agreements, obligations, warranties & legal terms.</p>
                <span class="policy-card-link">View →</span>
            </a>

            <!-- PRIVACY -->
            <a href="/policies/privacy" class="policy-card">
                <h3>Privacy Policy</h3>
                <p>How SharpLync collects, stores, secures & uses personal data.</p>
                <span class="policy-card-link">View →</span>
            </a>

            <!-- REMOTE SUPPORT -->
            <div class="policy-card">
                <h3>Remote Support Policy</h3>
                <p>Access handling, permissions & support security processes.</p>
                <span class="policy-card-link">Coming Soon</span>
            </div>

            <!-- SECURITY POLICY -->
            <div class="policy-card">
                <h3>Security Policy</h3>
                <p>Cybersecurity controls & SOC alignment. (Future)</p>
                <span class="policy-card-link">Coming Soon</span>
            </div>

        </div>

    </div>
</div>

<style>
.policy-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.policy-card {
    display: block;
    padding: 1.6rem;
    background: var(--sl-card);
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    color: var(--sl-text);
    text-decoration: none;
    transition: all 0.25s ease;
}

.policy-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 26px rgba(0,0,0,0.12);
}

.policy-card h3 {
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
    color: var(--sl-text);
}

.policy-card p {
    margin-bottom: 1rem;
    color: var(--sl-muted);
}

.policy-card-link {
    font-weight: 600;
    color: var(--sl-accent);
}
</style>

@endsection
