{{-- 
  Page: customers/dashboard.blade.php
  Version: v1.1
  Description: Customer Dashboard - post-login landing page for SharpLync users.
--}}

@extends('layouts.base')

@section('title', 'Dashboard')

@section('content')
<section class="onboard-container">
  <div class="onboard-card text-left">

    <h1 class="onboard-title">Welcome back, {{ Auth::guard('customer')->user()->first_name ?? 'User' }}!</h1>
    <p class="onboard-subtitle">
      You’re now logged into your SharpLync account.
    </p>

    {{-- Status message --}}
    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Quick Links --}}
    <div class="info-card" style="text-align:left; margin-top:1rem;">
      <h3 style="margin-bottom:0.5rem;">Your Account</h3>
      <ul style="list-style:none; padding-left:0;">
        <li><a href="{{ route('profile.edit') }}">Edit Profile</a></li>
        <li><a href="#">View Documents</a></li>
        <li><a href="#">Support & Helpdesk</a></li>
      </ul>
    </div>

    {{-- Logout Button --}}
    <form action="{{ route('customer.logout') }}" method="POST" style="margin-top:2rem;">
      @csrf
      <button type="submit" class="btn-primary w-full">Log Out</button>
    </form>

    <p class="onboard-note" style="margin-top:1rem;">
      SharpLync – Old School Support, <span class="highlight">Modern Results</span>
    </p>
  </div>
</section>
@endsection