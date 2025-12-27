@extends('admin.layouts.admin-layout')

@section('title', 'Customers')

@section('content')
  <div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <h2 class="fw-semibold">Customers</h2>
        <div class="sl-subtitle small">Search and manage customer profiles.</div>
      </div>

      <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex gap-2 align-items-center">
        <div class="input-group">
          <span class="input-group-text bg-white">Search</span>
          <input type="text" name="q" value="{{ $q }}" class="form-control"
                 placeholder="Search company, contact, email, phone" aria-label="Search customers">
        </div>

        <button class="btn btn-primary" type="submit">Search</button>
        @if($q !== '')
          <a class="btn btn-outline-secondary" href="{{ route('admin.customers.index') }}">Reset</a>
        @endif
      </form>
    </div>

    @if(session('status'))
      <div class="alert alert-success sl-card" role="alert">
        {{ session('status') }}
      </div>
    @endif

    <div class="card sl-card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Company</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($customers as $c)
                <tr>
                  <td class="fw-semibold">{{ $c->company_name ?? '—' }}</td>
                  <td>{{ $c->contact_name ?? '—' }}</td>
                  <td>{{ $c->email ?? '—' }}</td>
                  <td>{{ $c->phone ?? '—' }}</td>
                  <td>
                    <span class="badge text-bg-light border">
                      {{ $c->status ?? 'active' }}
                    </span>
                  </td>
                  <td class="text-end">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.customers.show', $c->id) }}">
                      View
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    No customers found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer bg-white border-0 d-flex justify-content-end">
        {{ $customers->links() }}
      </div>
    </div>
  </div>
@endsection
