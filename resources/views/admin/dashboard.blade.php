@extends('admin.layouts.admin-layout')

@section('title', 'Dashboard')

@section('content')

<div class="admin-dashboard">

    {{-- ===========================
         MINI HERO (Full-width inside MAIN)
    ============================ --}}
    <section class="admin-hero">
        <div class="admin-hero-inner">
            <div class="admin-hero-text">
                <div class="admin-hero-kicker">SharpLync Admin</div>
                <h1 class="admin-hero-title">Your central hub for customers, content &amp; support.</h1>
                <p class="admin-hero-sub">
                    Everything you need to manage SharpLync — customers, devices, tickets, CMS, and configuration —
                    all streamlined into one secure control centre.
                </p>
            </div>
        </div>
    </section>

    {{-- ===========================
         GLASS INFO PANEL
    ============================ --}}
    <section class="admin-glass-panel">
        <h2 class="panel-title">What you can manage here</h2>

        <ul class="panel-list">
            <li><span class="panel-dot"></span> Customer records and device audit history</li>
            <li><span class="panel-dot"></span> Support ticket queue & internal notes</li>
            <li><span class="panel-dot"></span> CMS pages, menus, services, SEO, and blog</li>
            <li><span class="panel-dot"></span> SMS verification, general SMS, and logs</li>
            <li><span class="panel-dot"></span> Pulse feed & component library</li>
        </ul>
    </section>

</div>

@endsection
