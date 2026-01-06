@extends('admin.layouts.admin-layout')

@section('title', 'SharpFleet Organisation Vehicles')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">Vehicles</h2>
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
                            <th>Registration</th>
                            <th>Make / Model</th>
                            <th class="text-end">Active</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $v)
                            <tr>
                                <td class="fw-semibold">{{ $v->name ?? '—' }}</td>
                                <td>{{ $v->registration_number ?? '—' }}</td>
                                <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '—' }}</td>
                                <td class="text-end">
                                    @if((int)($v->is_active ?? 0) === 1)
                                        <span class="badge text-bg-success">Yes</span>
                                    @else
                                        <span class="badge text-bg-secondary">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.sharpfleet.vehicles.show', $v->id) }}">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No vehicles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $vehicles->links() }}
        </div>
    </div>
</div>
@endsection
