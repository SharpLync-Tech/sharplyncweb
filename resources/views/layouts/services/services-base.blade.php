{{-- resources/views/layouts/services/services-base.blade.php --}}

@extends('layouts.base')

@push('styles')
    {{-- Services page stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/services/services.css') }}">
@endpush

@section('content')
    <div class="services-root">
        {{-- Hero section (per-page) --}}
        @yield('hero')

        {{-- Main services content --}}
        <main class="services-main">
            @yield('content')
        </main>
    </div>
@endsection

@push('scripts')
    {{-- Services page JS --}}
    <script src="{{ asset('js/services/services.js') }}" defer></script>
@endpush
