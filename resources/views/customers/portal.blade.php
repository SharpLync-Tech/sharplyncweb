{{-- 
  Page: customers/portal.blade.php
  Version: v1.5
  Last updated: 13 Nov 2025 by Max (ChatGPT)
  Description:
  Refined Customer Portal design — welcome moved to top nav,
  slim gradient band retained, elevated white content card.
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'SharpLync Portal')

@section('content')

<section class="portal-wrapper">
  
  {{-- Gradient header strip --}}
  <div class="portal-header">
      <div class="portal-header-inner">
          <h2>Account Portal</h2>
      </div>
  </div>

  {{-- Main card --}}
  <div class="portal-main-card">

      {{-- Tabs --}}
      <div class="portal-tabs">
          <button class="active" data-tab="details">
            <img src="/images/details.png" alt="Details" style="width:20px; height:20px; vertical-align:middle; margin-right:6px;">
            Details
          </button>
          <button data-tab="financial">
            <img src="/images/financial.png" alt="Financial" style="width:20px; height:20px; vertical-align:middle; margin-right:6px;">
            Financial
          </button>
          <button data-tab="security">
            <img src="/images/security.png" alt="Security" style="width:20px; height:20px; vertical-align:middle; margin-right:6px;">
            Security
          </button>
          <button data-tab="documents">
            <img src="/images/documents.png" alt="Documents" style="width:20px; height:20px; vertical-align:middle; margin-right:6px;">
            Documents
          </button>
          <button data-tab="support">
            <img src="/images/support.png" alt="Support" style="width:20px; height:20px; vertical-align:middle; margin-right:6px;">
            Support
          </button>
      </div>

      {{-- Details --}}
      <div id="details" class="portal-content active">
            <h3>Account Details</h3>
            <p>View and update your personal and company information.</p>
            <a href="{{ route('profile.edit') }}" class="btn-primary">Edit Profile</a>
      </div>


      {{-- Financial --}}
      <div class="portal-content" id="financial">
          <h3>Financial Overview</h3>
          <p>Review invoices, payment history, and manage billing preferences.</p>
          <a href="{{ route('customer.billing') }}" class="btn-primary">View Billing</a>
      </div>

      {{-- Security --}}
      <div class="portal-content" id="security">
          <h3>Security Settings</h3>
          <p>Manage passwords, enable two-factor authentication, and track login history.</p>
          <a href="{{ route('customer.security') }}" class="btn-primary">Manage Security</a>
      </div>

      {{-- Documents --}}
      <div class="portal-content" id="documents">
          <h3>Your Documents</h3>
          <p>Access signed agreements, policies, and uploaded files.</p>
          <a href="{{ route('customer.documents') }}" class="btn-primary">View Documents</a>
      </div>

      {{-- Support --}}
      <div class="portal-content" id="support">
          <h3>Support & Helpdesk</h3>
          <p>Need help? Contact SharpLync Support or open a service ticket.</p>
          <a href="{{ route('customer.support') }}" class="btn-primary">Get Support</a>
      </div>

      <p class="portal-note">
          SharpLync – Old School Support, <span class="highlight">Modern Results</span>
      </p>
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