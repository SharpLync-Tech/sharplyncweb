@extends('admin.layouts.admin-layout')

@section('title', 'SharpFleet Organisation Users')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">Users</h2>
            <div class="sl-subtitle small">{{ $organisation->name ?? 'Organisation' }} (ID: {{ $organisation->id }})</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.organisations.show', $organisation->id) }}">Back</a>
        </div>
    </div>

    <div class="card sl-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end">Driver</th>
                            <th>Trial ends</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="fw-semibold">{{ trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: '—' }}</td>
                                <td>{{ $u->email ?? '—' }}</td>
                                <td><span class="badge text-bg-light border">{{ $u->role ?? '—' }}</span></td>
                                <td class="text-end">{{ (int)($u->is_driver ?? 0) === 1 ? 'Yes' : 'No' }}</td>
                                <td>
                                    @if(!empty($u->trial_ends_at))
                                        {{ \Carbon\Carbon::parse($u->trial_ends_at, 'UTC')->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.sharpfleet.organisations.users.edit', [$organisation->id, $u->id]) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
