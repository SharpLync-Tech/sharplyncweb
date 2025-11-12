{{-- 
  Page: customers/portal.blade.php
  Version: v2.4.2 (Image-Lock Update)
  Last updated: 13 Nov 2025 by Max (ChatGPT)
  Description:
  - Fixed icon sizes to prevent scaling (Hulk mode).
  - Clean layout; logout handled via layout.
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Customer Portal')

@section('content')
<div class="portal-header">
  <div class="portal-header-inner">
    <h2>Account Portal</h2>
  </div>
</div>

<div class="portal-wrapper">
  <div class="portal-main-card">

    {{-- ===== Tabs ===== --}}
    <div class="portal-tabs">
      <button class="active" data-tab="details">
        <img class="icon-18" src="/images/details.png" alt="Details Icon"> Details
      </button>
      <button data-tab="financial">
        <img class="icon-18" src="/images/financial.png" alt="Financial Icon"> Financial
      </button>
      <button data-tab="security">
        <img class="icon-18" src="/images/security.png" alt="Security Icon"> Security
      </button>
      <button data-tab="documents">
        <img class="icon-18" src="/images/documents.png" alt="Documents Icon"> Documents
      </button>
      <button data-tab="support">
        <img class="icon-18" src="/images/support.png" alt="Support Icon"> Support
      </button>
    </div>

    {{-- ===== TAB CONTENT ===== --}}
    <div id="details" class="portal-content active">
      <h3>Account Details</h3>
      <p>View and update your personal and company information.</p>
      <a href="#" class="btn-primary">Edit Profile</a>
    </div>

    <div id="financial" class="portal-content">
      <h3>Financial</h3>
      <p>Billing and payment history will appear here.</p>
    </div>

    <div id="security" class="portal-content">
      <h3>Security Settings</h3>
      <p>Manage 2FA, password, and account security preferences.</p>
    </div>

    <div id="documents" class="portal-content">
      <h3>Documents</h3>
      <p>Access invoices, quotes, and uploaded files here.</p>
    </div>

    <div id="support" class="portal-content">
      <h3>Support</h3>
      <p>Submit support tickets or chat with SharpLync support.</p>
    </div>

    <p style="text-align:center; margin-top:2rem; font-size:0.9rem;">
      SharpLync – Old School Support, <span class="highlight">Modern Results</span>
    </p>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.querySelectorAll('.portal-tabs button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.portal-tabs button').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.portal-content').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });
</script>
@endsection
