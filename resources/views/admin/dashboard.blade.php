@extends('admin.layouts.admin-layout')

@section('title', 'Dashboard')

@section('content')
    <nav class="sidebar">
    <ul>

        {{-- ============================= --}}
        {{-- MAIN NAVIGATION --}}
        {{-- ============================= --}}
        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('admin.customers.index') }}">Customers</a></li>
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
        <li class="nav-section-label" style="
            margin-top: 20px;
            padding: 10px 15px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #A8C0D8;
            border-top: 1px solid rgba(255,255,255,0.15);
        ">
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
</nav>


    {{-- ======== MODAL SCRIPT ========= --}}
    <script>
        function openModal() {
            document.getElementById('sharpModal').classList.add('active');
        }
        function closeModal() {
            document.getElementById('sharpModal').classList.remove('active');
        }
    </script>
@endsection