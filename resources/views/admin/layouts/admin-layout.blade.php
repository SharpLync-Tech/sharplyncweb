<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync Admin Portal')</title>
    <link href="{{ asset('css/admin/sharplync-admin.css') }}?v=1.1" rel="stylesheet">
</head>
<body class="admin-portal">
<header class="admin-header">
    <h1>SharpLync Admin Portal</h1>

    <div class="header-right">
        <div class="header-profile">
            <img
                src="https://ui-avatars.com/api/?name={{ urlencode(session('admin_user')['displayName'] ?? 'SharpLync Admin') }}&background=0A2A4D&color=fff&size=36"
                alt="Profile">
            <span style="font-weight:700;">
                {{ session('admin_user')['displayName'] ?? 'SharpLync Admin' }}
            </span>
        </div>
        <a href="{{ url('/admin/logout') }}" class="logout-btn">Logout</a>
    </div>
</header>

<aside class="sidebar">

    {{-- Dashboard --}}
    <a href="{{ url('/admin/dashboard') }}"
       class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    {{-- Testimonials --}}
    <a href="{{ route('admin.testimonials.index') }}"
       class="{{ request()->is('admin/testimonials*') ? 'active' : '' }}">
        Testimonials
    </a>

    {{-- Devices --}}
    <a href="{{ route('admin.devices.index') }}"
       class="{{ request()->is('admin/devices') ? 'active' : '' }}">
        Devices – All
    </a>

    <a href="{{ route('admin.devices.unassigned') }}"
       class="{{ request()->is('admin/devices/unassigned') ? 'active' : '' }}">
        Devices – Unassigned
    </a>

    <a href="{{ route('admin.devices.import') }}"
       class="{{ request()->is('admin/devices/import') ? 'active' : '' }}">
       Devices – Import Audit
    </a>

    {{-- Pulse Feed --}}
    <a href="#" class="">Pulse Feed</a>

    {{-- Components --}}
    <a href="#" class="">Components</a>

    {{-- Settings --}}
    <a href="#" class="">Settings</a>

</aside>

<main class="admin-main">
    @yield('content')
</main>

</body>
</html>
