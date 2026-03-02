<!-- Marketing Page: Campaign Create -->
@extends('marketing.admin.layout')

@section('content')
@php
    $brandScope = $brandScope ?? 'both';
@endphp

<h1 style="margin-bottom:20px;">Create Campaign</h1>

@if ($errors->any())
    <div style="background:#ffecec;border:1px solid #ffb3b3;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Validation failed:</strong>
        <ul style="margin:10px 0 0 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div style="background:#ffecec;border:1px solid #ffb3b3;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div style="background:#e6f7ef;border:1px solid #b7e2c9;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Success:</strong> {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('marketing.admin.campaigns.store') }}">
    @csrf

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if($brandScope === 'both')
        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:6px;font-weight:600;">Brand</label>
            <select name="brand" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
                <option value="sl" {{ old('brand') === 'sl' ? 'selected' : '' }}>SharpLync</option>
                <option value="sf" {{ old('brand') === 'sf' ? 'selected' : '' }}>SharpFleet</option>
            </select>
        </div>
    @else
        <input type="hidden" name="brand" value="{{ $brandScope }}">
    @endif

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Campaign Name (internal)</label>
        <input type="text" name="name" required value="{{ old('name') }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
        <div style="font-size:12px;color:#666;margin-top:6px;">
            Example: "March 2026 AV Promo"
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Email Subject</label>
        <input type="text" name="subject" required value="{{ old('subject') }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Template</label>
        <select name="template_view" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
            <option value="">Default (brand template)</option>
            <option value="emails.marketing.templates.sl-basic">SharpLync - Basic</option>
            <option value="emails.marketing.templates.sf-basic">SharpFleet - Basic</option>
        </select>
        <div style="font-size:12px;color:#666;margin-top:6px;">
            If left blank, the brand default template will be used.
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Hero Image URL (optional)</label>
        <input type="text" name="hero_image" value="{{ old('hero_image') }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Email Content</label>
        <div id="marketing-quill-toolbar" class="quill-toolbar" style="margin-bottom:10px;">
            <span class="ql-formats">
                <select class="ql-header">
                    <option value="1">H1</option>
                    <option value="2">H2</option>
                    <option selected>Normal</option>
                </select>
            </span>
            <span class="ql-formats">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-list" value="ordered"></button>
                <button class="ql-list" value="bullet"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-link"></button>
                <button class="ql-image"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-clean"></button>
            </span>
        </div>
        <div id="marketing-quill-editor" class="quill-editor" style="background:#fff;border:1px solid #ccc;border-radius:6px;min-height:220px;padding:10px;"></div>
        <input type="hidden" name="body_html" id="body_html" value="{{ old('body_html') }}">
    </div>

    <button type="submit" class="btn-primary">
        Save Campaign
    </button>

    <a href="{{ route('marketing.admin.campaigns') }}" style="margin-left:12px;text-decoration:none;">
        Cancel
    </a>
</form>

<link href="{{ secure_asset('quill/quill.core.css') }}" rel="stylesheet">
<link href="{{ secure_asset('quill/quill.snow.css') }}" rel="stylesheet">
<script src="{{ secure_asset('quill/quill.min.js') }}"></script>
<script src="{{ secure_asset('js/marketing/marketing-quill.js') }}"></script>

@endsection
