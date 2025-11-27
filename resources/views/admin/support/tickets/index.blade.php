{{--
  Page: admin.support.tickets.index
  Description: Admin ticket list with filters
--}}

@extends('admin.layouts.admin-layout')

@section('title', 'Support Tickets')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-tickets.css') }}?v=1.0">
@endpush

@section('content')
<div class="container-fluid admin-tickets-page mt-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="mb-1 fw-semibold text-sharplync-navy">Support Tickets</h2>
            <p class="mb-0 text-muted small">
                View and manage all customer support tickets.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            @include('admin.support.tickets.partials.filters')
        </div>
    </div>

    {{-- Tickets table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-ticket-table">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-uppercase text-muted">#</th>
                            <th class="small text-uppercase text-muted">Subject</th>
                            <th class="small text-uppercase text-muted">Customer</th>
                            <th class="small text-uppercase text-muted text-center">Priority</th>
                            <th class="small text-uppercase text-muted text-center">Status</th>
                            <th class="small text-uppercase text-muted d-none d-md-table-cell">Last Update</th>
                            <th class="small text-uppercase text-muted d-none d-lg-table-cell">Created</th>
                            <th class="small text-uppercase text-muted text-center">Messages</th>
                            <th class="small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            @include('admin.support.tickets.partials.row', ['ticket' => $ticket])
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        <p class="mb-1 fw-semibold">No tickets found</p>
                                        <p class="small mb-0">
                                            Try adjusting your filters or check back later.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <p class="small text-muted mb-0">
                            Showing
                            <span class="fw-semibold">{{ $tickets->firstItem() }}</span>
                            to
                            <span class="fw-semibold">{{ $tickets->lastItem() }}</span>
                            of
                            <span class="fw-semibold">{{ $tickets->total() }}</span>
                            tickets
                        </p>
                        <div>
                            {{ $tickets->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-tickets.js') }}?v=1.0"></script>
@endpush
