<!-- 
  Page: test-threatpulse.blade.php
  Version: v2.0
  Last updated: 30 Oct 2025 by Max (ChatGPT)
  Description: Threat Pulse card integrated inside hero section, replacing ticker; mobile-friendly.
-->

@extends('layouts.base')

@section('title', 'SharpLync Threat Pulse | Live Security Intelligence')

@section('content')
<section class="hero">
    <div class="hero-text">
        <h1>SharpLync Threat Pulse<br><span>Live Security Intelligence</span></h1>
        <p>Testing live CISA threat feed integration — now embedded directly in the hero section with dynamic card updates.</p>
        <div class="hero-buttons">
            <button class="btn-accent" onclick="window.location.href='/'">Back to Home</button>
        </div>

        <!-- Integrated Threat Pulse Card -->
        <div class="hero-threat-card" id="heroThreatCard">
            <h3>Latest Cybersecurity Alert</h3>
            <div class="hero-threat-content" id="heroThreatContent">
                Loading latest alerts...
            </div>
        </div>
    </div>

    <div class="hero-image">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Circuit Graphic">
    </div>
</section>

<section class="tiles-section">
    <h2>What We Do Best</h2>
    <div class="tiles-wrapper">
        <div class="tile">
            <img src="{{ asset('images/support.png') }}" alt="IT Support & Cloud Icon" class="tile-icon">
            <h3>IT Support & Cloud</h3>
            <p>Reliable, responsive, and scalable support solutions for your business.</p>
        </div>
        <div class="tile">
            <img src="{{ asset('images/security.png') }}" alt="Security & Backup Icon" class="tile-icon">
            <h3>Security & Backup</h3>
            <p>Proactive protection and secure backup strategies for peace of mind.</p>
        </div>
        <div class="tile">
            <img src="{{ asset('images/infrastructure.png') }}" alt="Infrastructure Design Icon" class="tile-icon">
            <h3>Infrastructure Design</h3>
            <p>Tailored networks built for reliability and long-term performance.</p>
        </div>
    </div>
</section>

<script>
// Fetch and rotate live CISA threats in card
async function loadHeroThreats() {
    const contentEl = document.getElementById('heroThreatContent');
    try {
        const response = await fetch('https://api.rss2json.com/v1/api.json?rss_url=https://www.cisa.gov/news.xml');
        const data = await response.json();
        const items = data.items.slice(0, 10).map(i => i.title);
        let index = 0;

        function updateCard() {
            contentEl.style.opacity = 0;
            setTimeout(() => {
                contentEl.textContent = items[index];
                contentEl.style.opacity = 1;
                index = (index + 1) % items.length;
            }, 400);
        }

        updateCard();
        setInterval(updateCard, 6000);
    } catch (error) {
        console.error('Threat feed error:', error);
        contentEl.textContent = "⚠ Unable to fetch live alerts. Please try again later.";
    }
}
loadHeroThreats();
</script>
@endsection