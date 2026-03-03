<!-- Marketing Email: SharpLync Basic -->
@extends('emails.marketing.layouts.master')

@section('content')
@php
    $titleText = $title ?? null;
    $introText = $intro ?? null;
    $bodyText = $body ?? null;
@endphp

@if(!empty($titleText))
<h2 style="margin-top:0; font-size:20px; font-weight:600;">
    {{ $titleText }}
</h2>
@endif

@if(!empty($introText))
<p style="margin:15px 0 20px 0;">
    {{ $introText }}
</p>
@endif

<p style="margin:0 0 16px 0;">
    Hi {{ !empty($subscriber->first_name) ? $subscriber->first_name : 'there' }},
</p>

@if(!empty($bodyHtml))
<div style="margin:0 0 20px 0;">
    {!! $bodyHtml !!}
</div>
@elseif(!empty($bodyText))
<p style="margin:0 0 20px 0;">
    {!! nl2br(e($bodyText)) !!}
</p>
@endif

@if(!empty($ctaText) && !empty($ctaUrl))
<table cellpadding="0" cellspacing="0" style="margin:30px 0;">
<tr>
<td align="center">
    <a href="{{ $ctaUrl }}"
       style="background:#0A2A4D;
              color:#ffffff;
              padding:12px 24px;
              text-decoration:none;
              border-radius:6px;
              font-size:14px;
              font-weight:600;
              display:inline-block;">
        {{ $ctaText }}
    </a>
</td>
</tr>
</table>
@endif

<div style="text-align:center; margin:10px 0 0 0;">
    <a href="{{ url('/marketing/sharppulse') }}" style="color:#0ea5e9; text-decoration:none;">
        View this in browser · SharpPulse
    </a>
</div>

<table cellpadding="0" cellspacing="0" style="margin:12px auto 0 auto;">
<tr>
    <td align="center">
        <a href="https://www.facebook.com/SharpLync" style="display:inline-block;margin-right:10px;">
            <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" width="22" height="22" alt="Facebook" style="display:block;border:0;">
        </a>
        <a href="https://www.linkedin.com/company/sharplync" style="display:inline-block;">
            <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/linkedin.svg" width="22" height="22" alt="LinkedIn" style="display:block;border:0;">
        </a>
    </td>
</tr>
</table>

<div style="margin-top:12px; font-size:13px; color:#555; line-height:1.6; text-align:center;">
    SharpLync Pty Ltd · Managed IT Services<br>
    Stanthorpe QLD · Australia · 0492 014 463<br>
    <span style="display:inline-block;margin-top:8px;color:#666;">
        You are receiving this email because you are a current SharpLync customer or you subscribed to updates from SharpLync.
    </span>
    <div style="margin-top:8px;">
        <span style="display:inline-flex;align-items:center;gap:6px;">
            <span style="font-size:14px; line-height:1;">&#127760;</span>
            <a href="https://sharpfleet.com.au" style="color:#0ea5e9; text-decoration:none;">sharpfleet.com.au</a>
        </span>
        <span style="margin:0 8px;">|</span>
        @if(!empty($preferencesUrl))
            <a href="{{ $preferencesUrl }}" style="color:#0ea5e9; text-decoration:none;">Manage your Subscription</a>
        @else
            <span style="color:#999;">Manage your Subscription</span>
        @endif
    </div>
    <div style="margin-top:8px;">
        &copy; {{ date('Y') }} SharpLync
    </div>
</div>
@endsection
