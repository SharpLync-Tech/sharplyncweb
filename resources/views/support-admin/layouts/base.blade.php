<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SharpDesk Admin' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/support-admin/support-admin.css') }}?v=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')
</head>
<body class="support-admin-body">
<div class="support-admin-bg">
    <header class="support-admin-topbar">
        <div class="support-admin-topbar-left">
            <div class="support-admin-logo">SharpDesk</div>
        </div>
        <div class="support-admin-topbar-right">
            <span class="support-admin-topbar-user">
                {{ auth()->user()->name ?? 'Agent' }}
            </span>
            <a href="{{ route('logout') }}" class="support-admin-topbar-logout">Logout</a>
        </div>
    </header>

    <main class="support-admin-main">
        <div class="support-admin-wrapper">
            @yield('content')
        </div>
    </main>

    <footer class="support-admin-footer">
        &copy; {{ date('Y') }} SharpLync Pty Ltd &middot; All rights reserved
    </footer>
</div>

<script src="{{ asset('js/support-admin/support-admin.js') }}?v=1.0"></script>

@stack('scripts')
</body>
</html>
