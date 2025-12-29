@extends('admin.layouts.admin-layout')

@section('title', 'SharpFleet Platform Admin')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">SharpFleet Platform Admin</h2>
            <div class="sl-subtitle small">Subscribers (organisations), users, vehicles, and subscription details.</div>
        </div>

        <form method="GET" action="{{ route('admin.sharpfleet.platform') }}" class="d-flex gap-2 align-items-center">
            <div class="input-group">
                <span class="input-group-text bg-white">Search</span>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search organisation name or industry" aria-label="Search organisations">
            </div>
            <button class="btn btn-primary" type="submit">Go</button>
            @if($q !== '')
                <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.platform') }}">Reset</a>
            @endif
        </form>
    </div>

    <div class="card sl-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Organisation</th>
                            <th>Industry</th>
                            <th>Trial ends</th>
                            <th class="text-end">Users</th>
                            <th class="text-end">Vehicles</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organisations as $org)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $org->name ?? '—' }}
                                    <div class="text-muted small">ID: {{ $org->id }}</div>
                                </td>
                                <td>{{ $org->industry ?? '—' }}</td>
                                <td>
                                    @if(!empty($org->trial_ends_at))
                                        {{ \Carbon\Carbon::parse($org->trial_ends_at, 'UTC')->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">{{ (int)($org->users_count ?? 0) }}</td>
                                <td class="text-end">{{ (int)($org->vehicles_count ?? 0) }}</td>
                                <td class="text-end">
                                    <a class="btn btn-primary btn-sm" href="{{ route('admin.sharpfleet.organisations.show', $org->id) }}">Manage</a>
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.sharpfleet.organisations.edit', $org->id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No organisations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $organisations->links() }}
        </div>
    </div>
</div>
@endsection
