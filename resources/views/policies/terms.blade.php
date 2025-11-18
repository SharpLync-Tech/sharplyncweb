{{-- resources/views/policies/terms.blade.php --}}

@extends('layouts.policy-base')

@section('title', 'SharpLync | Terms & Conditions')

@section('policy-title')
    Terms & <span class="gradient">Conditions</span>
@endsection

{{-- These values will be passed from the controller later --}}
@section('pdf-url', $pdfUrl ?? null) 

@section('policy-content')
    {{-- This variable is where your converted PDF text will go. --}}
    {{-- We use {!! !!} to render HTML if the content is marked up (e.g., from PDF conversion) --}}
    @if(isset($content) && !empty($content))
        {!! $content !!}
    @else
        <p>The Terms & Conditions document is currently unavailable. Please check back later.</p>
    @endif
@endsection