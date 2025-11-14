<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About SharpLync</title>
    <!-- Include Bootstrap CSS (for layout, components, responsiveness) -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <!-- Include Animate.css for WOW animations -->
    <link rel="stylesheet" href="{{ asset('css/animate.min.css') }}">
    <!-- Include About page specific CSS (avoid using global sharplync.css to prevent conflicts) -->
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
</head>
<body>
    <!-- Header: use the main site header for consistency -->
    @include('partials.header')
    
    <!-- Main Content Area -->
    <main class="about-page-content">
        @yield('content')
    </main>
    
    <!-- Modals (placed at end of body to avoid nesting issues) -->
    @yield('modals')
    
    <!-- Footer: use the main site footer for consistency -->
    @include('partials.footer')
    
    <!-- Include jQuery (required for Bootstrap JS if using Bootstrap 4) -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <!-- Include Bootstrap JS (for modals and other components) -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <!-- Include WOW.js and initialize (for scroll reveal animations) -->
    <script src="{{ asset('js/wow.min.js') }}"></script>
    <script>
        new WOW().init();
    </script>
</body>
</html>
