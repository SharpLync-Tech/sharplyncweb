{{-- 
  Page: customers/portal.blade.php
  Version: v2.6 (Final Stable Portal Page)
  Description:
  - Uses cp-* scoped CSS
  - Includes tab navigation and responsive layout
--}}

@extends('customers.layouts.customer-layout')
@section('title', 'Customer Portal')

@section('content')
  <div class="cp-pagehead">
    <h2>Account Portal</h2>
  </div>

  <div class="cp-card">
    {{-- ===== Tabs ===== --}}
    <div class="cp-tabs" id="cpTabs">
      <button class="cp-active" data-cp-target="cp-details"><img src="/images/details.png" alt="">Details</button>
      <button data-cp-target="cp-financial"><img src="/images/financial.png" alt="">Financial</button>
      <button data-cp-target="cp-security"><img src="/images/security.png" alt="">Security</button>
      <button data-cp-target="cp-documents"><img src="/images/documents.png" alt="">Documents</button>
      <button data-cp-target="cp-support"><img src="/images/support.png" alt="">Support</button>
    </div>

    {{-- ===== Tab Panes ===== --}}
    <section id="cp-details" class="cp-pane cp-show">
      <h3>Account Details</h3>
      <p>View and update your personal and company information.</p>
      <a href="#" class="cp-btn">Edit Profile</a>
    </section>

    <section id="cp-financial" class="cp-pane">
      <h3>Financial</h3>
      <p>Billing and payment history will appear here.</p>
    </section>

    <section id="cp-security" class="cp-pane">
      <h3>Security Settings</h3>
      <p>Manage 2FA, password, and account security preferences.</p>
    </section>

    <section id="cp-documents" class="cp-pane">
      <h3>Documents</h3>
      <p>Access invoices, quotes, and uploaded files here.</p>
    </section>

    <section id="cp-support" class="cp-pane">
      <h3>Support</h3>
      <p>Submit support tickets or chat with SharpLync support.</p>
    </section>

    <p class="cp-footnote">
      SharpLync – Old School Support, <span class="cp-hl">Modern Results</span>
    </p>
  </div>
@endsection

@section('scripts')
<script>
  document.querySelectorAll('#cpTabs button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#cpTabs button').forEach(b => b.classList.remove('cp-active'));
      document.querySelectorAll('.cp-pane').forEach(p => p.classList.remove('cp-show'));
      btn.classList.add('cp-active');
      document.getElementById(btn.dataset.cpTarget).classList.add('cp-show');
    });
  });
</script>
@endsection
