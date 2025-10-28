{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('title', 'SharpLync Home')

@section('content')
<div class="container text-center mt-3">
    <section id="services" class="section services-row">
        <div class="card text-center">
            <h3 class="card-title">IT Support & Cloud</h3>
            <p>Reliable solutions that just work, so you can focus on business.</p>
        </div>

        <div class="card text-center">
            <h3 class="card-title">Security & Backup</h3>
            <p>Protecting your data, devices, and reputation from every angle.</p>
        </div>

        <div class="card text-center">
            <h3 class="card-title">Infrastructure Design</h3>
            <p>From Wi-Fi to full-scale networks, we build it right.</p>
        </div>

        <div class="card text-center">
            <h3 class="card-title">SharpLync SafeCheck</h3>
            <p>Free scam and phishing email checks, powered by AI.</p>
        </div>
    </section>
</div>
@endsection