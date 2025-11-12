{{-- 
  Page: customers/portal.blade.php
  Version: v2.7 (Account Details Data Integration)
  Last updated: 13 Nov 2025 by Max (ChatGPT)
  Description:
  - Adds real user info grid to Details tab
  - Removes green button
  - Fully responsive (auto-stacks on mobile)
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

      {{-- [NEW SECTION: Customer Info Table - 13 Nov 2025] --}}
      @php $user = Auth::guard('customer')->user(); @endphp
      <div class="cp-info-grid">
        <div><strong>Full Name:</strong><span>{{ $user->first_name ?? '—' }} {{ $user->last_name ?? '' }}</span></div>
        <div><strong>Email:</strong><span>{{ $user->email ?? '—' }}</span></div>
        @if(!empty($user->alt_email))
          <div><strong>Alternate Email:</strong><span>{{ $user->alt_email }}</span></div>
        @endif
        <div><strong>Account Status:</strong><span class="cp-badge {{ strtolower($user->account_status ?? 'active') }}">{{ ucfirst($user->account_status ?? 'Active') }}</span></div>
        <div><strong>Auth Provider:</strong><span>{{ $user->auth_provider ?? 'Local' }}</span></div>
        <div><strong>Verified:</strong><span>{{ $user->email_verified_at ? '✅ Yes' : '❌ No' }}</span></div>
        <div><strong>Last Login:</strong><span>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M Y, h:i A') : '—' }}</span></div>
        <div><strong>Login IP:</strong><span>{{ $user->login_ip ?? '—' }}</span></div>
        <div><strong>2FA Enabled:</strong><span>{{ $user->two_factor_secret ? 'Yes' : 'No' }}</span></div>
        @if(!empty($user->referral_code))
          <div><strong>Referral Code:</strong><span>{{ $user->referral_code }}</span></div>
        @endif
      </div>
      {{-- [END NEW SECTION] --}}

      <p class="cp-update-link"><a href="#">✎ Update my details</a></p>
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
