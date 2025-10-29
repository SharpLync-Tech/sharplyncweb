<!-- 
  Page: test-threatpulse.blade.php
  Version: v1.0
  Last updated: 30 Oct 2025 by Max (ChatGPT)
  Description: Test page for SharpLync Threat Pulse featuring live CISA RSS feed in ticker and card formats.
-->

@extends('layouts.base')

@section('title', 'SharpLync | Threat Pulse Test')

@section('content')

<!-- ====================== HERO (from v1.8 base) ====================== -->
<section class="hero">
    <div class="hero-text">
        <h1>SharpLync Threat Pulse<br><span>Live Security Intelligence</span></h1>
        <p>Testing live CISA threat feed integration — displaying both ticker and card versions for evaluation.</p>
        <div class="hero-buttons">
            <button class="btn-accent" onclick="window.location.href='/'">Back to Home</button>
        </div>
    </div>
    <div class="hero-image">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync Hero Image">
    </div>
</section>

<!-- ====================== THREAT PULSE SECTION ====================== -->
<section id="threatpulse" class="threatpulse-section">

    <h2 class="threatpulse-title">🛡️ SharpLync Threat Pulse — Live Feed Test</h2>

    <!-- Version A: Scrolling Ticker -->
    <div class="threat-ticker">
        <div class="threat-ticker-track" id="tickerTrack">
            <span>Loading live CISA feed...</span>
        </div>
    </div>

    <!-- Version B: Animated Card -->
    <div class="threat-card">
        <h3>Latest Cybersecurity Alerts</h3>
        <div id="threatCardContent" class="threat-card-content">
            <p>Loading feed...</p>
        </div>
    </div>

</section>

<!-- ====================== REGULAR CONTENT BELOW ====================== -->
<section id="services" class="tiles-section">
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

<section id="about" class="info-section">
    <div class="info-wrapper">
        <div class="info-card">
            <h2>About SharpLync</h2>
            <p>This test page showcases SharpLync’s ability to integrate real-time threat intelligence feeds — a glimpse into the next stage of proactive monitoring.</p>
        </div>
    </div>
</section>

<section id="contact" class="info-section">
    <div class="info-wrapper">
        <div class="info-card">
            <h2>Contact Us</h2>
            <p>Want to learn more about SharpLync Threat Pulse or IT security monitoring solutions?</p>
            <button class="btn">Get in Touch</button>
        </div>
    </div>
</section>

<!-- ====================== JS FEED SCRIPT ====================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const feedUrl = 'https://api.rss2json.com/v1/api.json?rss_url=https://www.cisa.gov/news.xml';
    const tickerTrack = document.getElementById('tickerTrack');
    const cardContent = document.getElementById('threatCardContent');

    const mockHeadlines = [
        "🧠 SharpLync detects phishing domains impersonating Microsoft 365",
        "🛡️ Trend Micro updates threat detection engine for new malware variant",
        "⚙️ SharpLync systems report 99.98% uptime this week",
        "📡 Increased ransomware activity detected in educational networks"
    ];

    function updateTicker(items) {
        tickerTrack.innerHTML = items.map(i => `<span>${i}</span>`).join(' • ');
    }

    function updateCard(items) {
        let index = 0;
        cardContent.innerHTML = `<p>${items[index]}</p>`;
        setInterval(() => {
            index = (index + 1) % items.length;
            cardContent.style.opacity = 0;
            setTimeout(() => {
                cardContent.innerHTML = `<p>${items[index]}</p>`;
                cardContent.style.opacity = 1;
            }, 400);
        }, 5000);
    }

    async function loadFeed() {
        try {
            const response = await fetch(feedUrl);
            const data = await response.json();
            if (data && data.items && data.items.length > 0) {
                const headlines = data.items.slice(0, 10).map(item => item.title);
                updateTicker(headlines);
                updateCard(headlines);
            } else {
                updateTicker(mockHeadlines);
                updateCard(mockHeadlines);
            }
        } catch (err) {
            console.error("Feed fetch failed, using mock data:", err);
            updateTicker(mockHeadlines);
            updateCard(mockHeadlines);
        }
    }

    loadFeed();
});
</script>

@endsection