<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync Admin Portal')</title>
    <link href="{{ asset('css/admin/sharplync-admin.css') }}" rel="stylesheet">

</head>
<body class="admin-portal">
    <header class="admin-header">
        <h1>⚡ SharpLync Admin Portal</h1>
        <a href="{{ url('/admin/logout') }}" class="logout-btn">Logout</a>
    </header>

    <aside class="sidebar">
        <a href="{{ url('/admin/dashboard') }}">🏠 Dashboard</a>
        <a href="#">💬 Pulse Feed</a>
        <a href="#">🧩 Components</a>
        <a href="#">⚙️ Settings</a>
    </aside>

    <main class="admin-main">
        @yield('content')
    </main>
</body>
</html>
