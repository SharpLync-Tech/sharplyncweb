<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | Services')</title>

    <link rel="stylesheet" href="/css/services/services.css">
</head>
<body class="services-root">

    @yield('hero')

    <main class="services-content">
        @yield('content')
    </main>

    <script src="/js/services/services.js"></script>
</body>
</html>
