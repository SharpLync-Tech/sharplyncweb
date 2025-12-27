@extends('admin.layouts.admin-layout')

@section('title', 'Customer Profile')

@section('content')

  <div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <h2 class="fw-semibold">{{ $customer->company_name ?? 'Customer' }}</h2>
        <div class="sl-subtitle small">Customer profile and recent activity.</div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.customers.index') }}">
          Back
        </a>
        <a class="btn btn-primary" href="{{ route('admin.customers.edit', $customer->id) }}">
          Edit
        </a>
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success sl-card" role="alert">
        {{ session('status') }}
      </div>
    @endif

    <div class="row g-3">

      <div class="col-12 col-xl-7">
        <div class="card sl-card">
          <div class="card-header py-3">
            <div class="fw-semibold">Overview</div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="text-muted small">Company</div>
                <div class="fw-semibold">{{ $customer->company_name ?? '—' }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-muted small">Contact</div>
                <div class="fw-semibold">{{ $customer->contact_name ?? '—' }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-muted small">Email</div>
                <div class="fw-semibold">{{ $customer->email ?? '—' }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-muted small">Phone</div>
                <div class="fw-semibold">{{ $customer->phone ?? '—' }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-muted small">Status</div>
                <div>
                  <span class="badge text-bg-light border">{{ $customer->status ?? 'active' }}</span>
                </div>
              </div>

              @if(!empty($customer->notes))
                <div class="col-12">
                  <div class="text-muted small">Notes</div>
                  <div class="mt-1" style="white-space: pre-wrap;">{{ $customer->notes }}</div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-5">
        <div class="card sl-card">
          <div class="card-header py-3">
            <div class="fw-semibold">Actions</div>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a class="btn btn-outline-primary" href="#" aria-disabled="true">
                Open Customer Portal (impersonate)
              </a>
              <a class="btn btn-outline-primary" href="#" aria-disabled="true">View Devices</a>
              <a class="btn btn-outline-primary" href="#" aria-disabled="true">Invoices</a>
              <a class="btn btn-outline-primary" href="#" aria-disabled="true">Notes</a>
              <a href="{{ url('/admin/support/sms') }}?phone={{ $customer->phone }}&customer_id={{ $customer->id }}" class="btn btn-primary">
                Send SMS
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card sl-card">
          <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <div class="fw-semibold">SMS History</div>
            <a href="{{ route('admin.support.sms.logs') }}" class="btn btn-outline-secondary btn-sm">
              View full log
            </a>
          </div>

          @php
            $smsLogs = \App\Models\Admin\SmsVerificationLog::where('customer_profile_id', $customer->id)
                  ->latest()
                  ->take(20)
                  ->get();
          @endphp

          <div class="card-body p-0">
            @if($smsLogs->isEmpty())
              <div class="p-4 text-muted">No SMS messages have been sent to this customer yet.</div>
            @else
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                        <td style="max-width: 520px;">
                          {{ \Illuminate\Support\Str::limit($log->message, 120) }}
                        </td>
                        <td>{{ $log->verification_code ?? '—' }}</td>
                        <td>
                          @if($log->status === 'success')
                            <span class="badge text-bg-success">Success</span>
                          @else
                            <span class="badge text-bg-danger">{{ $log->status }}</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>

    </div>
  </div>

@endsection
