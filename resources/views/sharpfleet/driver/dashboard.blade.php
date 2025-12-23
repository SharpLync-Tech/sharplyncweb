@extends('layouts.base')

@section('title', 'SharpFleet – Driver Dashboard')

@section('content')
<div style="max-width:900px;margin:60px auto;">

    <h1>Driver Dashboard</h1>

    @php
        $user = session('sharpfleet.user');
    @endphp

    <div style="background:#f4f7fb;padding:20px;border-radius:8px;margin:20px 0;">
        <p><strong>Name:</strong> {{ $user['name'] ?? '—' }}</p>
        <p><strong>Email:</strong> {{ $user['email'] ?? '—' }}</p>
        <p><strong>Organisation ID:</strong> {{ $user['organisation_id'] ?? '—' }}</p>
    </div>

    <p style="color:#555;">
        This is your driver workspace. From here you’ll be able to:
    </p>

    <ul>
        <li>Start and end trips</li>
        <li>View upcoming bookings</li>
        <li>Report vehicle faults</li>
    </ul>

    <hr style="margin:40px 0;">

    <form method="POST" action="/app/sharpfleet/logout">
        @csrf
        <button type="submit"
            style="background:#cc2f2f;color:white;padding:10px 16px;border:none;border-radius:6px;cursor:pointer;">
            Log out
        </button>
    </form>

</div>
@endsection
