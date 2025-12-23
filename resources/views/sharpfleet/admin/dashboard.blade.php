@extends('layouts.base')

@section('title', 'SharpFleet Admin')

@section('content')
<div style="max-width:900px;margin:60px auto;">

    <h1>SharpFleet Admin Dashboard</h1>

    <p>Welcome to SharpFleet. You are logged in as:</p>

    @php
        $user = session('sharpfleet.user');
    @endphp

    @if ($user)
        <div style="background:#f4f7fb;padding:20px;border-radius:8px;margin:20px 0;">
            <p><strong>Name:</strong> {{ $user['name'] ?? '—' }}</p>
            <p><strong>Email:</strong> {{ $user['email'] ?? '—' }}</p>
            <p><strong>Role:</strong> {{ ucfirst($user['role'] ?? '—') }}</p>
            <p><strong>Organisation ID:</strong> {{ $user['organisation_id'] ?? '—' }}</p>
        </div>
    @else
        <p style="color:red;">
            ⚠️ No SharpFleet session found. You should not be seeing this page.
        </p>
    @endif

    <hr>

    <ul>
        <li>
            <a href="/app/sharpfleet/admin/register">
                Register new admin (stub)
            </a>
        </li>
    </ul>

    <hr>

    <form method="POST" action="/app/sharpfleet/logout">
        @csrf
        <button type="submit"
            style="background:#cc2f2f;color:white;padding:10px 16px;border:none;border-radius:6px;cursor:pointer;">
            Log out of SharpFleet
        </button>
    </form>

</div>
@endsection
