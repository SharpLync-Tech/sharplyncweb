@extends('admin.layouts.admin-layout')

@section('title', 'Dashboard')

@section('content')
    <h2>Welcome back, {{ session('admin_user')['displayName'] ?? 'SharpLync Admin' }} 👋</h2>
    <p>This is your secure admin dashboard.</p>

    <div style="margin-top: 25px;">
        <h3>Quick Links</h3>
        <ul>
            <li><a href="#">Manage Pulse Feed</a></li>
            <li><a href="#">View Logs</a></li>
            <li><a href="#">System Settings</a></li>
        </ul>
    </div>
@endsection