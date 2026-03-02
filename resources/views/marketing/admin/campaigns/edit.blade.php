<!-- Marketing Page: Campaign Edit -->
@extends('marketing.admin.layout')

@section('content')
@php
    $brandScope = $brandScope ?? 'both';
@endphp

<h1 style="margin-bottom:20px;">Edit Campaign</h1>

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

<form method="POST" action="{{ route('marketing.admin.campaigns.update', $campaign->id) }}">
    @csrf

    @if($brandScope === 'both')
        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:6px;font-weight:600;">Brand</label>
            <select name="brand" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
                <option value="sl" {{ old('brand', $campaign->brand) === 'sl' ? 'selected' : '' }}>SharpLync</option>
                <option value="sf" {{ old('brand', $campaign->brand) === 'sf' ? 'selected' : '' }}>SharpFleet</option>
            </select>
        </div>
    @else
        <input type="hidden" name="brand" value="{{ $campaign->brand }}">
    @endif

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Campaign Name (internal)</label>
        <input type="text" name="name" required value="{{ old('name', $campaign->name) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Email Subject</label>
        <input type="text" name="subject" required value="{{ old('subject', $campaign->subject) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Template</label>
        <select name="template_view" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
            <option value="" {{ old('template_view', $campaign->template_view) === null ? 'selected' : '' }}>Default (brand template)</option>
            <option value="emails.marketing.templates.sl-basic" {{ old('template_view', $campaign->template_view) === 'emails.marketing.templates.sl-basic' ? 'selected' : '' }}>SharpLync - Basic</option>
            <option value="emails.marketing.templates.sf-basic" {{ old('template_view', $campaign->template_view) === 'emails.marketing.templates.sf-basic' ? 'selected' : '' }}>SharpFleet - Basic</option>
        </select>
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Hero Image URL (optional)</label>
        <input type="text" name="hero_image" value="{{ old('hero_image', $campaign->hero_image) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Body HTML</label>
        <textarea name="body_html" rows="14" required
                  style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">{{ old('body_html', $campaign->body_html) }}</textarea>
    </div>

    <button type="submit" class="btn-primary">
        Update Campaign
    </button>

    <a href="{{ route('marketing.admin.campaigns') }}" style="margin-left:12px;text-decoration:none;">
        Cancel
    </a>
</form>

@endsection
