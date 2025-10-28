{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('title', 'SharpLync Home')

@section('content')
<div class="container text-center mt-3">
    <section id="services" class="section services-grid">
        <div class="card text-center">
            <h3 class="card-title">IT Support & Cloud</h3>
            <p>Reliable solutions that just work, so you can focus on business.</p>
            <a href="/services/it-support" class="btn-secondary mt-2">Read More</a>
        </div>

        <div class="card text-center">
            <h3 class="card-title">Security & Backup</h3>
            <p>Protecting your data, devices, and reputation from every angle.</p>
            <a href="/services/security" class="btn-secondary mt-2">Read More</a>
        </div>

        <div class="card text-center">
            <h3 class="card-title">Infrastructure Design</h3>
            <p>From Wi-Fi to full-scale networks, we build it right.</p>
            <a href="/services/infrastructure" class="btn-secondary mt-2">Read More</a>
        </div>

        <div class="card text-center">
            <h3 class="card-title">SharpLync SafeCheck</h3>
            <p>Free scam and phishing email checks, powered by AI.</p>
            <a href="/services/safecheck" class="btn-secondary mt-2">Read More</a>
        </div>
    </section>
</div>
@endsection
