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

  {{-- Next actions (placeholders) --}}
  <div class="admin-card">
    <h3>Next Actions</h3>
    <div class="actions" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;">
      <a class="btn btn-primary" href="#" aria-disabled="true">Open Customer Portal (impersonate)</a>
      <a class="btn btn-primary" href="#" aria-disabled="true">View Devices</a>
      <a class="btn btn-primary" href="#" aria-disabled="true">Invoices</a>
      <a class="btn btn-primary" href="#" aria-disabled="true">Notes</a>
    </div>
  </div>
@endsection
