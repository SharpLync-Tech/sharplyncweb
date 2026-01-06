@php
    $sfOrganisationId = (int) session('sharpfleet.user.organisation_id');
@endphp

<div class="sharpfleet-nav-primary">
    <a href="/app/sharpfleet/admin" class="sharpfleet-nav-link {{ request()->is('app/sharpfleet/admin') ? 'is-active' : '' }}">Dashboard</a>

    <div class="sharpfleet-nav-dropdown">
        <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/vehicles*') || request()->is('app/sharpfleet/admin/reminders*') || request()->is('app/sharpfleet/admin/safety-checks*') ? 'is-active' : '' }}">Fleet</button>
        <div class="sharpfleet-nav-dropdown-menu">
            <a href="/app/sharpfleet/admin/vehicles" class="sharpfleet-nav-dropdown-item">Vehicles</a>
            <a href="/app/sharpfleet/admin/reminders" class="sharpfleet-nav-dropdown-item">Reminders</a>
            <a href="/app/sharpfleet/admin/safety-checks" class="sharpfleet-nav-dropdown-item">Safety Checks</a>
        </div>
    </div>

    <div class="sharpfleet-nav-dropdown">
        <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/reports*') || request()->is('app/sharpfleet/admin/faults*') ? 'is-active' : '' }}">Reports</button>
        <div class="sharpfleet-nav-dropdown-menu">
            <a href="/app/sharpfleet/admin/reports/trips" class="sharpfleet-nav-dropdown-item">Trip Reports</a>
            <a href="/app/sharpfleet/admin/faults" class="sharpfleet-nav-dropdown-item">Faults</a>
        </div>
    </div>

    <div class="sharpfleet-nav-dropdown">
        <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/help*') || request()->is('app/sharpfleet/admin/about*') || request()->is('app/sharpfleet/admin/account*') ? 'is-active' : '' }}">Help</button>
        <div class="sharpfleet-nav-dropdown-menu">
            <a href="/app/sharpfleet/admin/help" class="sharpfleet-nav-dropdown-item">Instructions</a>
            <a href="/app/sharpfleet/admin/about" class="sharpfleet-nav-dropdown-item">About</a>
            <a href="/app/sharpfleet/admin/account" class="sharpfleet-nav-dropdown-item">Account</a>
        </div>
    </div>

    <a href="/app/sharpfleet/driver" class="sharpfleet-nav-link {{ request()->is('app/sharpfleet/driver*') ? 'is-active' : '' }}">Driver</a>
</div>
