{{-- resources/views/policies/terms.blade.php --}}
@extends('layouts.policy-base')

@section('title', 'SharpLync | Terms & Conditions')

@section('policy-title')
    Terms & <span class="gradient">Conditions</span>
@endsection

{{-- These values will be passed from the controller later --}}
@section('pdf-url', $pdfUrl ?? null)

@section('policy-content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        {{-- This variable is where your converted PDF text will go. --}}
                        {{-- We use {!! !!} to render HTML if the content is marked up (e.g., from PDF conversion) --}}
                        @if(isset($content) && !empty($content))
                            {!! $content !!}
                        @else
                            <p>The Terms & Conditions document is currently unavailable. Please check back later.</p>
                        @endif

                        {{-- Download link at the bottom --}}
                        @if(isset($pdfUrl) && !empty($pdfUrl))
                            <div class="text-center mt-4">
                                <a href="{{ $pdfUrl }}" class="btn btn-primary" download>Download the official document (PDF)</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection