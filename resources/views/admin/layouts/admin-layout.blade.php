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

{{-- ⭐ FIX: Proper wrapper to hold sidebar + main content --}}
<div class="admin-container" style="display:flex;min-height:calc(100vh - 70px);">

    {{-- Sidebar --}}
    <aside class="sidebar">

        {{-- Dashboard --}}
        <a href="{{ url('/admin/dashboard') }}"
           class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        {{-- Customers --}}
        <a href="{{ route('admin.customers.index') }}"
           class="{{ request()->is('admin/customers*') ? 'active' : '' }}">
            Customers
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
        <a href="{{ route('admin.pulse.index') }}"
           class="{{ request()->is('admin/pulse*') ? 'active' : '' }}">
            Pulse Feed
        </a>

        {{-- Components --}}
        <a href="{{ route('admin.components.index') }}"
           class="{{ request()->is('admin/components*') ? 'active' : '' }}">
            Components
        </a>

        {{-- Settings --}}
        <a href="#" class="">Settings</a>

        {{-- CMS Section Header --}}
        <div class="sidebar-section-title">CMS MANAGEMENT</div>

        <a href="{{ route('admin.cms.menu.index') }}" class="{{ request()->is('admin/cms/menu*') ? 'active' : '' }}">
            Menu Items
        </a>

        <a href="{{ route('admin.cms.pages.index') }}" class="{{ request()->is('admin/cms/pages*') ? 'active' : '' }}">
            Pages
        </a>

        <a href="{{ route('admin.cms.services.index') }}" class="{{ request()->is('admin/cms/services*') ? 'active' : '' }}">
            Services
        </a>

        <a href="{{ route('admin.cms.footer.index') }}" class="{{ request()->is('admin/cms/footer*') ? 'active' : '' }}">
            Footer Links
        </a>

        <a href="{{ route('admin.cms.contact.index') }}" class="{{ request()->is('admin/cms/contact*') ? 'active' : '' }}">
            Contact Info
        </a>

        <a href="{{ route('admin.cms.seo.index') }}" class="{{ request()->is('admin/cms/seo*') ? 'active' : '' }}">
            SEO Meta
        </a>

        <a href="{{ route('admin.cms.about.sections.index') }}"
           class="{{ request()->is('admin/cms/about/sections*') ? 'active' : '' }}">
            About Sections
        </a>

        <a href="{{ route('admin.cms.about.values.index') }}"
           class="{{ request()->is('admin/cms/about/values*') ? 'active' : '' }}">
            About Values
        </a>

        <a href="{{ route('admin.cms.about.timeline.index') }}"
           class="{{ request()->is('admin/cms/about/timeline*') ? 'active' : '' }}">
            About Timeline
        </a>

    </aside>

    {{-- Main Content --}}
    <main class="admin-main" style="flex:1;padding:30px;">
        @yield('content')
    </main>

</div>

</body>
</html>
