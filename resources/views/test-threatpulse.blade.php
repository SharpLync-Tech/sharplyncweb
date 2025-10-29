<!-- 
  Page: test-threatpulse.blade.php
  Version: v2.2
  Last updated: 30 Oct 2025 by Max (ChatGPT)
  Description: SharpLync Pulse with red–teal animated pulse icon and live feed card.
-->

@extends('layouts.base')

@section('title', 'SharpLync Pulse | Live Security Intelligence')

@section('content')
<section class="hero">
  <div class="hero-text">
    <h1>
      <span class="pulse-dot"></span> SharpLync Pulse<br>
      <span>Live Security Intelligence</span>
    </h1>
    <p>Real-time insights from trusted cybersecurity and IT intelligence sources — refreshed automatically.</p>
    
    <div class="hero-buttons">
      <button class="btn-accent" onclick="window.location.href='/'">Back to Home</button>
    </div>

    <!-- Integrated SharpLync Pulse Card -->
    <div class="hero-threat-card" id="heroThreatCard">
      <h3><span class="pulse-dot"></span> SharpLync Pulse</h3>
      <div class="hero-threat-content" id="heroThreatContent">
        Loading live updates...
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
// ===================== SharpLync Pulse Live Feed =====================
async function loadHeroThreats() {
  const contentEl = document.getElementById('heroThreatContent');
  try {
    const response = await fetch('/proxy-cisa.php'); // Adjust if filename differs
    const data = await response.json();
    const items = (data?.items || []).slice(0, 10);

    if (items.length === 0) {
      contentEl.textContent = "No live updates available.";
      return;
    }

    let index = 0;
    function rotate() {
      const item = items[index];
      contentEl.innerHTML = `
        <a href="${item.link}" target="_blank" style="color:#2CBFAE;text-decoration:none;">
          ${item.title}
        </a><br>
        <small style="color:#ccc;">${item.source} — ${new Date(item.date).toLocaleDateString()}</small>
      `;
      index = (index + 1) % items.length;
    }

    rotate();
    setInterval(rotate, 8000);
  } catch (err) {
    console.error("Pulse feed error:", err);
    contentEl.textContent = "Unable to fetch live updates. Please try again later.";
  }
}
loadHeroThreats();
</script>
@endsection
