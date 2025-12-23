@extends('layouts.base')

@section('title', 'SharpFleet Admin')

@section('content')
<div style="max-width:900px;margin:60px auto;">
    <h1>SharpFleet Admin Dashboard</h1>

    <p>If you can see this, routing + views are working 🎉</p>

    <pre>
User:
{{ print_r(auth()->user(), true) }}
    </pre>

    <ul>
        <li><a href="/app/sharpfleet/admin/register">Register new admin (stub)</a></li>
    </ul>
</div>
@endsection
