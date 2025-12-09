@extends('admin.layouts.admin-layout')

@section('title', 'Customer Profile')

@section('content')

  {{-- Header --}}
  <div class="admin-top-bar">
    <h2>{{ $customer->company_name ?? 'Customer' }}</h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a class="btn btn-accent" href="{{ route('admin.customers.index') }}">Back to Customers</a>
      <a class="btn btn-primary" href="{{ route('admin.customers.edit', $customer->id) }}">Edit Customer</a>
    </div>
  </div>

  {{-- Flash status --}}
  @if(session('status'))
    <div class="admin-card" style="border-left:4px solid #2CBFAE;">
      {{ session('status') }}
    </div>
  @endif

  {{-- Overview --}}
  <div class="admin-card">
    <h3>Overview</h3>

    <div class="overview-grid">

      <div class="overview-item">
        <strong>Company</strong><br>{{ $customer->company_name ?? '—' }}
      </div>

      <div class="overview-item">
        <strong>Contact</strong><br>{{ $customer->contact_name ?? '—' }}
      </div>

      <div class="overview-item">
        <strong>Email</strong><br>{{ $customer->email ?? '—' }}
      </div>

      <div class="overview-item">
        <strong>Phone</strong><br>{{ $customer->phone ?? '—' }}
      </div>

      <div class="overview-item">
        <strong>Status</strong><br>{{ $customer->status ?? 'active' }}
      </div>

      @if(!empty($customer->notes))
      <div class="overview-item" style="grid-column:1 / -1;">
        <strong>Notes</strong>
        <div style="margin-top:6px;white-space:pre-wrap;">{{ $customer->notes }}</div>
      </div>
      @endif

    </div>
  </div>

  {{-- Next Actions --}}
  <div class="admin-card">
    <h3>Next Actions</h3>

    <div class="actions" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;">
      <a class="btn btn-primary" href="#" aria-disabled="true">Open Customer Portal (impersonate)</a>
      <a class="btn btn-primary" href="#" aria-disabled="true">View Devices</a>
      <a class="btn btn-primary" href="#" aria-disabled="true">Invoices</a>
      <a class="btn btn-primary" href="#" aria-disabled="true">Notes</a>

      {{-- SMS Button --}}
      <a href="{{ url('/admin/support/sms') }}?phone={{ $customer->phone }}&customer_id={{ $customer->id }}"
         class="btn btn-primary">
         📱 Send SMS
      </a>
    </div>
  </div>

  {{-- CUSTOMER SMS HISTORY --}}
  <div class="admin-card" style="margin-top:24px;">
      <h3>SMS History</h3>

      @php
          $smsLogs = \App\Models\Admin\SmsVerificationLog::where('customer_profile_id', $customer->id)
                      ->latest()
                      ->take(20)
                      ->get();
      @endphp

      @if($smsLogs->isEmpty())
          <div style="padding:12px;color:#596a7c;">
              No SMS messages have been sent to this customer yet.
          </div>
      @else
          <div style="overflow-x:auto;margin-top:10px;">
              <table class="table">
                  <thead>
                      <tr>
                          <th>Date</th>
                          <th>Admin</th>
                          <th>Message</th>
                          <th>Code</th>
                          <th>Status</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($smsLogs as $log)
                          <tr>
                              <td>{{ $log->created_at->format('d M Y, H:i') }}</td>

                              <td>{{ $log->admin_name ?? '—' }}</td>

                              <td style="max-width:350px;">
                                  {{ \Illuminate\Support\Str::limit($log->message, 60) }}
                              </td>

                              <td>{{ $log->verification_code ?? '—' }}</td>

                              <td>
                                  @if($log->status === 'success')
                                      <span class="sms-badge-success">Success</span>
                                  @else
                                      <span class="sms-badge-failure">{{ $log->status }}</span>
                                  @endif
                              </td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
      @endif

      <div style="margin-top:10px;">
          <a href="{{ route('admin.support.sms.logs') }}" class="btn btn-accent btn-sm">
              View Full SMS Log
          </a>
      </div>
  </div>

@endsection
