{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.services.services-base')

@section('title', 'SharpLync | Services (Mock)')

@section('hero')
<header class="services-hero">
    <img src="{{ asset('images/sharplync-logo.png') }}" 
         class="services-hero-logo" 
         alt="SharpLync Logo">

    <h1>What We Do</h1>
    <h2>Sharp Solutions</h2>

    <p>
        From the Granite Belt to the Cloud — smart systems, secure solutions, 
        and real people who care about getting IT right.
    </p>
</header>
@endsection

@section('content')
<section class="services-section">
    @foreach ($categories as $cat)
        @include('services.components.tile', ['cat' => $cat])
    @endforeach
</section>
@endsection
