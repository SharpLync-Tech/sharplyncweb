{{-- 
  Page: customers/portal.blade.php
  Version: v2.5 (Clean Card + Tab Reset)
  Description:
  - Restores solid white card and gradient background
  - Tabs normal again, no transparency bleed
--}}

@extends('customers.layouts.customer-layout')
@section('title','Customer Portal')

@section('content')
<div class="portal-header">
  <div class="portal-header-inner">
    <h2>Account Portal</h2>
  </div>
</div>

<div class="portal-wrapper">
  <div class="portal-main-card">
    <div class="portal-tabs">
      <button class="active" data-tab="details"><img src="/images/details.png" alt="">Details</button>
      <button data-tab="financial"><img src="/images/financial.png" alt="">Financial</button>
      <button data-tab="security"><img src="/images/security.png" alt="">Security</button>
      <button data-tab="documents"><img src="/images/documents.png" alt="">Documents</button>
      <button data-tab="support"><img src="/images/support.png" alt="">Support</button>
    </div>

    <div id="details" class="portal-content active">
      <h3>Account Details v1</h3>
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

    <p class="portal-footer-note">
      SharpLync – Old School Support, <span class="highlight">Modern Results</span>
    </p>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.querySelectorAll('.portal-tabs button').forEach(btn=>{
    btn.addEventListener('click',()=>{
      document.querySelectorAll('.portal-tabs button').forEach(b=>b.classList.remove('active'));
      document.querySelectorAll('.portal-content').forEach(c=>c.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });
</script>
@endsection
