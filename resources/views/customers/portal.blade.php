{{-- 
  Page: customers/portal.blade.php
  Version: v2.6.1 (Restored Background & Layout Integrity)
  Description:
  - Restores .cp-root background container
  - Fixes white-screen bug and full background loss
  - Maintains responsive baseline verified as stable
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
@endphp

@section('content')
<div class="cp-root">
  <header class="cp-header">
    <div class="cp-logo">
      <img src="/images/logo-light.png" alt="SharpLync Logo">
    </div>
    <div class="cp-welcome">
      Welcome, {{ explode(' ', $fullName)[0] ?? 'User' }}
      <form method="POST" action="{{ route('logout') }}" class="cp-logout-inline">
        @csrf
        <button type="submit" title="Log out">⏻</button>
      </form>
    </div>
  </header>

  <main class="cp-main">
    <div class="cp-card">
      <div class="cp-pagehead">
        <h2>Account Portal</h2>
      </div>

      {{-- Tabs --}}
      <div class="cp-tabs">
        <button class="cp-active" data-cp-target="cp-details">Details</button>
        <button data-cp-target="cp-financial">Financial</button>
        <button data-cp-target="cp-security">Security</button>
        <button data-cp-target="cp-documents">Documents</button>
        <button data-cp-target="cp-support">Support</button>
      </div>

      {{-- Details --}}
      <section id="cp-details" class="cp-pane cp-show">
        <div class="cp-grid">
          <div class="cp-card-panel">
            <div class="cp-panel-head">
              <h3>Account Details</h3>
              <a href="{{ route('profile.edit') }}" class="cp-btn sm">Edit Profile</a>
            </div>

            <dl class="cp-def-grid">
              <dt>Full Name</dt><dd>{{ $fullName }}</dd>
              <dt>Email</dt><dd>{{ $email }}</dd>
              <dt>Alt Email</dt><dd>{{ $altEmail }}</dd>
              <dt>Account Status</dt><dd>{{ ucfirst($status) }}</dd>
              <dt>Auth Provider</dt><dd>{{ $provider }}</dd>
              <dt>Verified</dt><dd>{{ $verified }}</dd>
            </dl>
          </div>

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
          </div>
        </div>
      </section>

      {{-- Other Tabs --}}
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
  </main>
</div>
@endsection

@section('scripts')
<script>
  document.querySelectorAll('.cp-tabs button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.cp-tabs button').forEach(b => b.classList.remove('cp-active'));
      document.querySelectorAll('.cp-pane').forEach(p => p.classList.remove('cp-show'));
      btn.classList.add('cp-active');
      document.getElementById(btn.dataset.cpTarget).classList.add('cp-show');
    });
  });
</script>
@endsection
