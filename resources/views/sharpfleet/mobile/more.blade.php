@extends('sharpfleet.mobile.layouts.app')

@section('title', 'More')

@section('content')
<section class="sf-mobile-dashboard">

    <h1 class="sf-mobile-title">More</h1>
    <p class="sf-mobile-subtitle">Settings and extras.</p>

    {{-- Links --}}
    <a href="/app/sharpfleet/mobile/bookings" class="sf-mobile-list-item">
        <span>Bookings</span>
    </a>

    <a href="/app/sharpfleet/mobile/help" class="sf-mobile-list-item">
        <span>Help</span>
    </a>

    <a href="/app/sharpfleet/mobile/support" class="sf-mobile-list-item">
        <span>Support</span>
    </a>

    <a href="/app/sharpfleet/mobile/about" class="sf-mobile-list-item">
        <span>About SharpFleet</span>
    </a>

    {{-- Appearance --}}
    <div class="sf-mobile-card">
        <h3 class="sf-mobile-card-title">Appearance</h3>
        <p class="sf-mobile-card-text">Use your device setting or pick a theme.</p>

        <div class="sf-theme-toggle" id="sfThemeToggle">
            <label class="sf-theme-option">
                <input type="radio" name="sfTheme" value="system">
                System
            </label>
            <label class="sf-theme-option">
                <input type="radio" name="sfTheme" value="dark">
                Dark
            </label>
            <label class="sf-theme-option">
                <input type="radio" name="sfTheme" value="light">
                Light
            </label>
        </div>
    </div>

    <a href="/app/sharpfleet/logout" class="sf-mobile-list-item">
        <span>Log out</span>
    </a>

</section>

<script>
(function () {
    const wrapper = document.getElementById('sfThemeToggle');
    if (!wrapper || !window.sfSetTheme) return;

    const radios = wrapper.querySelectorAll('input[name="sfTheme"]');
    const current = window.sfGetThemeMode ? window.sfGetThemeMode() : 'system';

    function syncActive() {
        radios.forEach(radio => {
            const label = radio.closest('.sf-theme-option');
            if (label) label.classList.toggle('is-active', radio.checked);
        });
    }

    radios.forEach(radio => {
        radio.checked = radio.value === current;
        radio.addEventListener('change', () => {
            if (!radio.checked) return;
            window.sfSetTheme(radio.value);
            syncActive();
        });
    });

    syncActive();
})();
</script>
@endsection
