{{-- 
  Page: customers/portal.blade.php
  Version: v1.2
  Last updated: 13 Nov 2025 by Max (ChatGPT)
  Description:
  Refined Customer Portal design — hero removed, integrated welcome in top nav,
  gradient background for main content, and elevated white card sections.
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'SharpLync Portal')

@section('content')

<section class="portal-wrapper">

  {{-- ===== PORTAL HEADER (now gradient background) ===== --}}
  <div class="portal-header">
      <div class="portal-header-inner">
          <h2>Account Portal</h2>
          <p>Welcome, {{ Auth::guard('customer')->user()->first_name ?? 'User' }} 👋</p>
      </div>
  </div>

  {{-- ===== MAIN PORTAL CARD ===== --}}
  <div class="portal-main-card">

      {{-- TABS NAVIGATION --}}
      <div class="portal-tabs">
          <button class="active" data-tab="details">🧍 Details</button>
          <button data-tab="financial">💳 Financial</button>
          <button data-tab="security">🔐 Security</button>
          <button data-tab="documents">📄 Documents</button>
          <button data-tab="support">💬 Support</button>
      </div>

      {{-- TAB: DETAILS --}}
      <div class="portal-content active" id="details">
          <h3>Account Details</h3>
          <p>View and update your personal and company information.</p>
          <a href="{{ route('profile.edit') }}" class="btn-primary">Edit Profile</a>
      </div>

      {{-- TAB: FINANCIAL --}}
      <div class="portal-content" id="financial">
          <h3>Financial Overview</h3>
          <p>Review invoices, payment history, and manage billing preferences.</p>
          <a href="{{ route('customer.billing') }}" class="btn-primary">View Billing</a>
      </div>

      {{-- TAB: SECURITY --}}
      <div class="portal-content" id="security">
          <h3>Security Settings</h3>
          <p>Manage passwords, enable two-factor authentication, and track login history.</p>
          <a href="{{ route('customer.security') }}" class="btn-primary">Manage Security</a>
      </div>

      {{-- TAB: DOCUMENTS --}}
      <div class="portal-content" id="documents">
          <h3>Your Documents</h3>
          <p>Access signed agreements, policies, and uploaded files.</p>
          <a href="{{ route('customer.documents') }}" class="btn-primary">View Documents</a>
      </div>

      {{-- TAB: SUPPORT --}}
      <div class="portal-content" id="support">
          <h3>Support & Helpdesk</h3>
          <p>Need help? Contact SharpLync Support or open a service ticket.</p>
          <a href="{{ route('customer.support') }}" class="btn-primary">Get Support</a>
      </div>

      {{-- LOGOUT --}}
      <form action="{{ route('customer.logout') }}" method="POST" class="logout-form">
          @csrf
          <button type="submit" class="btn-primary w-full">Log Out</button>
      </form>

      <p class="portal-note">SharpLync – Old School Support, <span class="highlight">Modern Results</span></p>
  </div>

</section>

@endsection

@section('scripts')
<script>
  document.querySelectorAll('.portal-tabs button').forEach(btn => {
      btn.addEventListener('click', () => {
          document.querySelectorAll('.portal-tabs button').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.portal-content').forEach(tab => tab.classList.remove('active'));
          btn.classList.add('active');
          document.getElementById(btn.dataset.tab).classList.add('active');
      });
  });
</script>
@endsection