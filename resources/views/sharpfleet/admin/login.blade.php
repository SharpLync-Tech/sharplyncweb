@extends('layouts.base')

@section('title', 'SharpFleet Admin Login')

@section('content')
<div style="max-width:420px;margin:60px auto;">
    <h1>SharpFleet Admin</h1>
    <p>Admin login (temporary)</p>

    <form method="POST" action="/app/sharpfleet/login">
        @csrf

        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div style="margin-top:10px;">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button style="margin-top:20px;">Login</button>
    </form>
</div>
@endsection
