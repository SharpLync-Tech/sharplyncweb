{{-- resources/views/policies/privacy.blade.php --}}

@extends('layouts.policy-base')

@section('title', 'SharpLync | Privacy Policy')

@section('policy-title')
    Privacy <span class="gradient">Policy</span>
@endsection

{{-- These values will be passed from the controller later --}}
@section('pdf-url', $pdfUrl ?? null) 

@section('policy-content')
    {{-- This variable is where your converted PDF text will go. --}}
    @if(isset($content) && !empty($content))
        {!! $content !!}
    @else
        <p>The Privacy Policy document is currently unavailable. Please check back later.</p>
    @endif
@endsection