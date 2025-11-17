@extends('admin.layouts.admin-layout')

@section('title', 'Customers')

@section('content')
  {{-- Header + search --}}
  <div class="admin-top-bar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
    <h2>Customers</h2>
    <form method="GET" action="{{ route('admin.customers.index') }}" style="display:flex;gap:8px;align-items:center;">
      <input type="text" name="q" value="{{ $q }}" placeholder="Search company, contact, email, phone"
             style="padding:10px 12px;border:1px solid #d6dee6;border-radius:8px;min-width:280px;">
      <button class="btn btn-primary" type="submit">Search</button>
      @if($q !== '')
        <a class="btn btn-accent" href="{{ route('admin.customers.index') }}">Reset</a>
      @endif
    </form>
  </div>

  {{-- Flash status --}}
  @if(session('status'))
    <div class="admin-card" style="border-left:4px solid #2CBFAE;">
      {{ session('status') }}
    </div>
  @endif

  {{-- Table --}}
  <div class="admin-card">
    <div class="table-responsive">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid #e7edf3;">
            <th style="padding:10px;">Company</th>
            <th style="padding:10px;">Contact</th>
            <th style="padding:10px;">Email</th>
            <th style="padding:10px;">Phone</th>
            <th style="padding:10px;">Status</th>
            <th style="padding:10px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($customers as $c)
            <tr style="border-bottom:1px solid #f0f3f6;">
              <td style="padding:10px;">{{ $c->company_name ?? '—' }}</td>
              <td style="padding:10px;">{{ $c->contact_name ?? '—' }}</td>
              <td style="padding:10px;">{{ $c->email ?? '—' }}</td>
              <td style="padding:10px;">{{ $c->phone ?? '—' }}</td>
              <td style="padding:10px;">
                <span style="padding:4px 8px;border-radius:999px;background:#F0F6FA;border:1px solid #D7E6F2;">
                  {{ $c->status ?? 'active' }}
                </span>
              </td>
              <td style="padding:10px;">
                <a class="btn btn-primary" href="{{ route('admin.customers.show', $c->id) }}">View Profile</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="padding:14px;">No customers found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:14px;">
      {{ $customers->links() }}
    </div>
  </div>
@endsection
