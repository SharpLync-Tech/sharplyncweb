@extends('layouts.sharpfleet')

@section('title', 'SharpFleet')

@section('sharpfleet-content')
<div style="max-width:800px;margin:80px auto;text-align:center;">

    <h1 style="font-size:32px;margin-bottom:10px;">
        SharpFleet
    </h1>

    <p style="color:#555;font-size:16px;margin-bottom:40px;">
        Fleet management for drivers and administrators.
    </p>

    <div style="margin-bottom:30px;">
        <a href="/app/sharpfleet/login"
           style="display:inline-block;background:#2CBFAE;color:white;
                  padding:14px 28px;border-radius:6px;
                  text-decoration:none;font-weight:600;">
            Log in
        </a>
    </div>

    <div style="font-size:14px;color:#666;">
        <a href="/app/sharpfleet/admin/register"
           style="color:#0A2A4D;text-decoration:none;">
            Register your organisation
        </a>
        <span style="opacity:0.5;">(coming soon)</span>
    </div>

</div>
@endsection
