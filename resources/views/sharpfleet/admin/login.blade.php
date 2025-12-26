@extends('layouts.base')

@section('title', 'SharpFleet Admin Login')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width:420px;">
        <div class="auth-header">
            <h1 class="auth-title">SharpFleet Admin</h1>
            <p class="auth-subtitle">Admin login (temporary)</p>
        </div>
        <form method="POST" action="/app/sharpfleet/login" class="auth-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full mt-2">Login</button>
        </form>
    </div>
</div>
@endsection
