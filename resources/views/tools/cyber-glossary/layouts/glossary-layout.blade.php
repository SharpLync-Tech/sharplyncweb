{{-- 
|--------------------------------------------------------------------------
| SharpLync Cyber Glossary Layout
| Tool: Cyber Glossary
| Version: v1.0
|--------------------------------------------------------------------------
| Purpose:
| - Provides a dedicated layout for the Cyber Glossary tool
| - Inherits SharpLync global layout
| - Loads SharpLync base styles + tool-specific styles
|--------------------------------------------------------------------------
--}}

@extends('layouts.base')

@push('styles')
    {{-- SharpLync global styling --}}
    <link rel="stylesheet" href="{{ asset('css/sharplync.css') }}">

    {{-- Cyber Glossary tool styling --}}
    <link rel="stylesheet" href="{{ asset('css/tools/cyber-glossary.css') }}">
@endpush

@section('content')
<div class="tool-wrapper cyber-glossary">

    <header class="tool-header">
        <h1>Cybersecurity, in Plain English</h1>
        <p class="tool-intro">
            Simple explanations of common cybersecurity terms — no jargon, no scare tactics.
        </p>
    </header>

    <main class="tool-content">
        @yield('tool-content')
    </main>

</div>
@endsection
