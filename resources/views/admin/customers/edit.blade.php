@extends('admin.layouts.admin-layout')

@section('title', 'Edit Customer')

@section('content')
  {{-- Header --}}
  <div class="admin-top-bar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
    <h2>Edit: {{ $customer->company_name ?? 'Customer' }}</h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a class="btn btn-accent" href="{{ route('admin.customers.show', $customer->id) }}">← Cancel</a>
    </div>
  </div>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="admin-card" style="border-left:4px solid #b40000;">
      <strong>There were some problems with your input:</strong>
      <ul style="margin-top:8px; padding-left:18px;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Form --}}
  <div class="admin-card">
    <form method="POST" action="{{ route('admin.customers.update', $customer->id) }}" style="display:grid;gap:16px;">
      @csrf
      @method('PUT')

      {{-- optimistic concurrency token --}}
      <input type="hidden" name="updated_at" value="{{ optional($customer->updated_at)->format('Y-m-d H:i:s') }}">

      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
        <div>
          <label for="company_name" style="display:block;font-weight:600;margin-bottom:6px;">Company Name *</label>
          <input id="company_name" name="company_name" type="text" required
                 value="{{ old('company_name', $customer->company_name) }}"
                 style="width:100%;padding:10px 12px;border:1px solid #d6dee6;border-radius:8px;">
        </div>

        <div>
          <label for="contact_name" style="display:block;font-weight:600;margin-bottom:6px;">Contact Name</label>
          <input id="contact_name" name="contact_name" type="text"
                 value="{{ old('contact_name', $customer->contact_name) }}"
                 style="width:100%;padding:10px 12px;border:1px solid #d6dee6;border-radius:8px;">
        </div>

        <div>
          <label for="email" style="display:block;font-weight:600;margin-bottom:6px;">Email</label>
          <input id="email" name="email" type="email"
                 value="{{ old('email', $customer->email) }}"
                 style="width:100%;padding:10px 12px;border:1px solid #d6dee6;border-radius:8px;">
        </div>

        <div>
          <label for="phone" style="display:block;font-weight:600;margin-bottom:6px;">Phone</label>
          <input id="phone" name="phone" type="text"
                 value="{{ old('phone', $customer->phone) }}"
                 style="width:100%;padding:10px 12px;border:1px solid #d6dee6;border-radius:8px;">
        </div>

        <div>
          <label for="status" style="display:block;font-weight:600;margin-bottom:6px;">Status</label>
          <select id="status" name="status"
                  style="width:100%;padding:10px 12px;border:1px solid #d6dee6;border-radius:8px;background:#fff;">
            @php
              $status = old('status', $customer->status ?? 'active');
              $options = ['active' => 'Active', 'inactive' => 'Inactive', 'prospect' => 'Prospect'];
            @endphp
            @foreach($options as $val => $label)
              <option value="{{ $val }}" @selected($status === $val)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div style="grid-column:1/-1;">
          <label for="notes" style="display:block;font-weight:600;margin-bottom:6px;">Notes</label>
          <textarea id="notes" name="notes" rows="5"
                    style="width:100%;padding:10px 12px;border:1px solid #d6dee6;border-radius:8px;">{{ old('notes', $customer->notes) }}</textarea>
        </div>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:4px;">
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a class="btn btn-accent" href="{{ route('admin.customers.show', $customer->id) }}">Cancel</a>
      </div>
    </form>
  </div>
@endsection
