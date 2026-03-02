<!-- Marketing Email: SharpFleet Basic -->
@extends('emails.marketing.layouts.master')

@section('content')
@php
    $titleText = $title ?? ($subject ?? ($campaign->subject ?? 'SharpFleet Update'));
    $introText = $intro ?? null;
    $bodyText = $body ?? null;
@endphp

<h2 style="margin-top:0; font-size:20px; font-weight:600;">
    {{ $titleText }}
</h2>

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
@endsection
