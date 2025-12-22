@extends('layouts.base')

@section('title', 'SharpFleet')

@section('content')
<div style="max-width:900px;margin:80px auto;padding:40px;">
    <h1>🚗 SharpFleet</h1>

    <p style="color:#6b7280;margin-bottom:20px;">
        SharpFleet module is alive.
    </p>

    <div style="background:#f4f7fb;padding:20px;border-radius:12px;">
        <p><strong>Status:</strong> ✅ Loaded</p>
        <p><strong>User:</strong> {{ auth()->user()->email }}</p>
        <p><strong>Time:</strong> {{ now() }}</p>
    </div>
</div>
@endsection
