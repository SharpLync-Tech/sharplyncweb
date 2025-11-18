{{-- 
  Page: services/mock.blade.php
  Version: v1.1 (Uses Services Layout + Mock Data)
  Description:
  - Extends services-base, which itself extends layouts.base
  - Renders the services grid with mock categories
--}}

@extends('layouts.services.services-base')

@section('services-content')

<section class="services-grid">
    @foreach ($categories as $cat)
        @include('services.components.tile', ['cat' => $cat])
    @endforeach
</section>

@endsection
