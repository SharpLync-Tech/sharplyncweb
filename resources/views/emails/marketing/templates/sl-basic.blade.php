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
<td align="left">
    <a href="{{ $ctaUrl }}"
       style="background:#0ea5e9;
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

<table cellpadding="0" cellspacing="0" style="margin:20px 0 0 0;">
<tr>
    <td align="left">
        <a href="https://www.facebook.com/SharpLync" style="display:inline-block;margin-right:10px;">
            <img src="https://sharplync.com.au/images/facebook.png" width="24" height="24" alt="Facebook" style="display:block;border:0;">
        </a>
        <a href="https://www.linkedin.com/company/sharplync" style="display:inline-block;">
            <img src="https://sharplync.com.au/images/linkedin.png" width="24" height="24" alt="LinkedIn" style="display:block;border:0;">
        </a>
    </td>
</tr>
</table>

<div style="margin-top:16px; font-size:13px; color:#555; line-height:1.5;">
    <a href="{{ url('/marketing/sharppulse') }}" style="color:#0ea5e9; text-decoration:none;">SharpPulse</a>
    <div style="margin-top:8px;">
        SharpLync Pty Ltd<br>
        Managed IT Services<br>
        Stanthorpe QLD · Australia<br>
        0492 014 463
    </div>
</div>
@endsection
