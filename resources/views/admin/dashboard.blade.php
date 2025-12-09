@extends('admin.layouts.admin-layout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', 'Dashboard')

@section('content')
<div class="admin-shell">

    {{-- ===========================
         MINI HERO (Gradient)
    ============================ --}}
    <section class="admin-hero">
        <div class="admin-hero-inner">
            <div class="admin-hero-text">
                <div class="admin-hero-kicker">SharpLync Admin</div>
                <h1 class="admin-hero-title">Your central hub for customers, content &amp; support.</h1>
                <p class="admin-hero-sub">
                    From this dashboard you can access customers, devices, support tickets, CMS content,
                    Pulse, components, and settings — all in one secure place.
                </p>
            </div>

            <aside class="admin-hero-panel">
                <div class="admin-hero-panel-title">What you can manage here</div>
                <ul class="admin-hero-panel-list">
                    <li>
                        <span class="admin-hero-dot"></span>
                        <span>Customer records and device audit history.</span>
                    </li>
                    <li>
                        <span class="admin-hero-dot"></span>
                        <span>Support ticket queue and conversations.</span>
                    </li>
                    <li>
                        <span class="admin-hero-dot"></span>
                        <span>Website content, blog, and knowledge base.</span>
                    </li>
                    <li>
                        <span class="admin-hero-dot"></span>
                        <span>Internal Pulse feed, components, and settings.</span>
                    </li>
                </ul>
            </aside>
        </div>
    </section>

    {{-- ===========================
         MAIN LAYOUT
    ============================ --}}
    <div class="admin-main">

        {{-- SIDEBAR (sexy glass, OG links intact) --}}
        <nav class="sidebar admin-sidebar">
            <div class="admin-sidebar-inner">
                <ul>

                    {{-- ============================= --}}
                    {{-- MAIN NAVIGATION --}}
                    {{-- ============================= --}}
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.customers.index') }}">Customers</a></li>

                    <h5 class="mt-4 mb-2 text-muted text-uppercase small">Support</h5>
                    <ul class="list-unstyled">
                        <li>
                            <a href="{{ route('admin.support.tickets.index') }}">
                                🧾 Support Tickets
                            </a>
                        </li>
                    </ul>

                    <li><a href="{{ route('admin.testimonials.index') }}">Testimonials</a></li>

                    <li><a href="{{ route('admin.devices.index') }}">Devices – All</a></li>
                    <li><a href="{{ route('admin.devices.unassigned') }}">Devices – Unassigned</a></li>
                    <li><a href="{{ route('admin.devices.import') }}">Devices – Import Audit</a></li>

                    <li><a href="{{ route('admin.pulse.index') }}">Pulse Feed</a></li>
                    <li><a href="{{ route('admin.components.index') }}">Components</a></li>
                    <li><a href="{{ route('admin.settings.index') }}">Settings</a></li>


                    {{-- ============================= --}}
                    {{-- CMS SECTION LABEL --}}
                    {{-- ============================= --}}
                    <li class="nav-section-label">
                        CMS Management
                    </li>

                    {{-- ============================= --}}
                    {{-- CMS MASTER GROUP --}}
                    {{-- ============================= --}}
                    <li><a href="{{ route('admin.cms.menu.index') }}">Menu Items</a></li>
                    <li><a href="{{ route('admin.cms.pages.index') }}">Pages</a></li>
                    <li><a href="{{ route('admin.cms.services.index') }}">Services</a></li>
                    <li><a href="{{ route('admin.cms.footer.index') }}">Footer Links</a></li>
                    <li><a href="{{ route('admin.cms.contact.index') }}">Contact Info</a></li>
                    <li><a href="{{ route('admin.cms.seo.index') }}">SEO Meta</a></li>

                    {{-- ABOUT --}}
                    <li><a href="{{ route('admin.cms.about.sections.index') }}">About Sections</a></li>
                    <li><a href="{{ route('admin.cms.about.values.index') }}">About Values</a></li>
                    <li><a href="{{ route('admin.cms.about.timeline.index') }}">About Timeline</a></li>

                    {{-- BLOG --}}
                    <li><a href="{{ route('admin.cms.blog.categories.index') }}">Blog Categories</a></li>
                    <li><a href="{{ route('admin.cms.blog.posts.index') }}">Blog Posts</a></li>

                    {{-- KB --}}
                    <li><a href="{{ route('admin.cms.kb.categories.index') }}">KB Categories</a></li>
                    <li><a href="{{ route('admin.cms.kb.articles.index') }}">KB Articles</a></li>

                </ul>
            </div>
        </nav>

        {{-- RIGHT CONTENT AREA (clean, ready for future use) --}}
        <section class="admin-main-content">
            <h2 class="admin-main-title">Welcome to the SharpLync Admin Portal</h2>
            <p class="admin-main-sub">
                Use the navigation on the left to jump into customers, devices, support, or CMS management.
                This area can be expanded over time with stats, recent activity, or quick actions — but for now
                everything works exactly as before, just with a cleaner layout.
            </p>

            <div class="admin-main-placeholder">
                <p>
                    💡 Tip: You can pin this dashboard in your browser so you always land on the same
                    control centre when managing SharpLync.
                </p>
                <p>
                    The links in the sidebar are identical to your previous setup — nothing about your workflow,
                    routes, or controllers has changed. Only the visuals have been upgraded.
                </p>
            </div>
        </section>

    </div>

</div>

{{-- ======== MODAL SCRIPT (unchanged) ========= --}}
<script>
    function openModal() {
        document.getElementById('sharpModal').classList.add('active');
    }
    function closeModal() {
        document.getElementById('sharpModal').classList.remove('active');
    }
</script>
@endsection
