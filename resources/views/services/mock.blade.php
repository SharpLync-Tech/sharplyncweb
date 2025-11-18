@extends('layouts.services.services-base')

@section('title', 'SharpLync Services (Mock)')

@section('content')

<section class="services-grid">
    @foreach ($categories as $cat)
        @include('services.components.tile', ['cat' => $cat])
    @endforeach
</section>

@endsection
