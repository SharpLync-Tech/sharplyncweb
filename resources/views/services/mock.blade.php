@extends('layouts.services.services-base')

@section('title', 'SharpLync Services (Mock)')

@section('hero')
<header class="services-hero">
    <h1>What We Do</h1>
    <p>Your business. Sharper, faster, safer.</p>
</header>
@endsection

@section('content')

<section class="services-grid">
    @foreach ($categories as $cat)
        @include('services.components.tile', ['cat' => $cat])
    @endforeach
</section>

@endsection
