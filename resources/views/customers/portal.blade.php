{{-- 
  Page: customers/portal.blade.php
  Version: v3.1 (UI Polish & Alignment Update)
  Date locked: 12 Nov 2025, 6:42 PM
  Notes:
  - Retains two-column layout
  - Minor spacing, alignment and header refinements
  - No structural or functional changes
--}}

@extends('customers.layouts.customer-layout')
@section('title', 'Customer Portal')

@php
  $user = Auth::guard('customer')->user();

  $fullName   = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '—';
  $email      = $user->email ?? '—';
  $altEmail   = $user->alt_email ?? '—';
  $status     = $user->account_status ?? '—';
  $provider   = $user->auth_provider ?? '—';
  $verified   = $user->email_verified_at ? 'Yes' : 'No';
  $lastLogin  = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M Y, h:i A') : '—';
  $loginIp    = $user->login_ip ?? '—';
  $twoFA      = $user->two_factor_secret ? 'Enabled' : 'No';
  $twoFAConf  = $user->two_factor_confirmed_at ? \Carbon\Carbon::parse($user->two_factor_confirmed_at)->format('d M Y, h:i A') : '—';
  $accepted   = $user->accepted_terms_at ? \Carbon\Carbon::parse($user->accepted_terms_at)->format('d M Y, h:i A') : '—';
  $refCode    = $user->referral_code ?? '—';
  $sspin      = $user->sspin ?? '—';
@endphp

@section('content')
  <div class="cp-pagehead">
    <h2>Account Portal</h2>
  </div>

  <div class="cp-main">
    <div class="cp-card">
      {{-- ===== Tabs ===== --}}
      <div class="cp-tabs" id="cpTabs">
        <button class="cp-active" data-cp-target="cp-details"><img src="/images/details.png" alt="">Details</button>
        <button data-cp-target="cp-financial"><img src="/images/financial.png" alt="">Financial</button>
        <button data-cp-target="cp-security"><img src="/images/security.png" alt="">Security</button>
        <button data-cp-target="cp-documents"><img src="/images/documents.png" alt="">Documents</button>
        <button data-cp-target="cp-support"><img src="/images/support.png" alt="">Support</button>
      </div>

      {{-- ===== Details Tab ===== --}}
      <section id="cp-details" class="cp-pane cp-show">
        <div class="cp-grid">
          {{-- LEFT PANEL --}}
          <div class="cp-card-panel">
            <div class="cp-panel-head">
              <h3>Customer Details</h3>
              <a href="{{ route('profile.edit') }}" class="cp-btn sm">Edit Profile</a>
            </div>

            <dl class="cp-def-grid">
              <dt>Full Name</dt><dd>{{ $fullName }}</dd>
              <dt>Email</dt><dd>{{ $email }}</dd>
              <dt>Alt Email</dt><dd>{{ $altEmail }}</dd>
              <dt>Account Status</dt><dd>{{ ucfirst($status) }}</dd>
              <dt>Auth Provider</dt><dd>{{ $provider }}</dd>
              <dt>Verified</dt><dd>{{ $verified }}</dd>
              <dt>Accepted Terms</dt><dd>{{ $accepted }}</dd>
              <dt>Referral Code</dt><dd>{{ $refCode }}</dd>
              <dt>SSPIN</dt><dd>{{ $sspin }}</dd>
            </dl>
          </div>

          {{-- RIGHT PANEL --}}
          <div class="cp-card-panel">
            <div class="cp-panel-head">
              <h3>Recent Activity</h3>
            </div>

            <div class="cp-kv-row">
              <div class="cp-kv">
                <span class="cp-kv-label">Last Login</span>
                <span class="cp-kv-value">{{ $lastLogin }}</span>
              </div>
              <div class="cp-kv">
                <span class="cp-kv-label">Login IP</span>
                <span class="cp-kv-value">{{ $loginIp }}</span>
              </div>
            </div>

            <div class="cp-divider"></div>

            <div class="cp-kv-row">
              <div class="cp-kv">
                <span class="cp-kv-label">2FA Status</span>
                <span class="cp-badge {{ $twoFA === 'Enabled' ? 'ok' : 'muted' }}">{{ $twoFA }}</span>
              </div>
              <div class="cp-kv">
                <span class="cp-kv-label">2FA Confirmed</span>
                <span class="cp-kv-value">{{ $twoFAConf }}</span>
              </div>
            </div>

            <div class="cp-tile-grid">
              <div class="cp-tile">
                <div class="cp-tile-title">Latest Invoice</div>
                <div class="cp-tile-sub">Coming soon</div>
                <a class="cp-link" href="javascript:void(0)" aria-disabled="true">View details</a>
              </div>
              <div class="cp-tile">
                <div class="cp-tile-title">Support Tickets</div>
                <div class="cp-tile-sub">Coming soon</div>
                <a class="cp-link" href="javascript:void(0)" aria-disabled="true">Open portal</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- ===== Other Tabs (unchanged placeholders) ===== --}}
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

      <p class="cp-footnote">SharpLync — Old School Support, <span class="cp-hl">Modern Results</span></p>
    </div>
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
