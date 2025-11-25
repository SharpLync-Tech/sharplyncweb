@extends('admin.layouts.admin-layout')

@section('title', 'Customer Profile')

@section('content')
  {{-- Header --}}
  <div class="admin-top-bar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
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
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;margin-top:10px;">
      <div style="background:#fff;border:1px solid #e7edf3;border-radius:10px;padding:14px;">
        <strong>Company</strong><br>{{ $customer->company_name ?? '—' }}
      </div>
      <div style="background:#fff;border:1px solid #e7edf3;border-radius:10px;padding:14px;">
        <strong>Contact</strong><br>{{ $customer->contact_name ?? '—' }}
      </div>
      <div style="background:#fff;border:1px solid #e7edf3;border-radius:10px;padding:14px;">
        <strong>Email</strong><br>{{ $customer->email ?? '—' }}
      </div>
      <div style="background:#fff;border:1px solid #e7edf3;border-radius:10px;padding:14px;">
        <strong>Phone</strong><br>{{ $customer->phone ?? '—' }}
      </div>
      <div style="background:#fff;border:1px solid #e7edf3;border-radius:10px;padding:14px;">
        <strong>Status</strong><br>{{ $customer->status ?? 'active' }}
      </div>

      @if(!empty($customer->notes))
      <div style="background:#fff;border:1px solid #e7edf3;border-radius:10px;padding:14px;grid-column:1/-1;">
        <strong>Notes</strong>
        <div style="margin-top:6px;white-space:pre-wrap;">{{ $customer->notes }}</div>
      </div>
      @endif
    </div>
  </div>

  {{-- Next actions --}}
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

  {{-- ⭐ CUSTOMER SMS HISTORY PANEL --}}
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
              <table class="table" style="width:100%;border-collapse:collapse;">
                  <thead>
                      <tr style="background:#f1f4f8;">
                          <th style="padding:8px 10px;">Date</th>
                          <th style="padding:8px 10px;">Admin</th>
                          <th style="padding:8px 10px;">Message</th>
                          <th style="padding:8px 10px;">Code</th>
                          <th style="padding:8px 10px;">Status</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($smsLogs as $log)
                          <tr style="border-bottom:1px solid #e8eef3;">
                              <td style="padding:8px 10px;">
                                  {{ $log->created_at->format('d M Y, H:i') }}
                              </td>

                              <td style="padding:8px 10px;">
                                  {{ $log->admin_name ?? '—' }}
                              </td>

                              <td style="padding:8px 10px;max-width:350px;">
                                  {{ \Illuminate\Support\Str::limit($log->message, 60) }}
                              </td>

                              <td style="padding:8px 10px;">
                                  {{ $log->verification_code ?? '—' }}
                              </td>

                              <td style="padding:8px 10px;">
                                  @if($log->status === 'success')
                                      <span style="background:#2CBFAE;color:white;padding:4px 8px;border-radius:6px;font-size:12px;">
                                          Success
                                      </span>
                                  @else
                                      <span style="background:#dc3545;color:white;padding:4px 8px;border-radius:6px;font-size:12px;">
                                          {{ $log->status }}
                                      </span>
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
