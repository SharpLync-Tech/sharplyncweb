{{-- 
  Page: resources/views/admin/support/tickets/index.blade.php
  Version: v1.0 (Phase 1)
  Description:
  - Admin Support Ticket List
  - Filters: search, status, priority, sort
  - Paginated table view
--}}

@extends('admin.layouts.admin-layout')

@section('title', 'Support Tickets')

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/admin-tickets.css') }}">
@endpush

@section('content')
    <div class="container-fluid mt-4 admin-ticket-index">

        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
            <div>
                <h2 class="mb-1 fw-semibold">Support Tickets</h2>
                <p class="text-muted mb-0 small">
                    View and manage all customer support tickets in one place.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        {{-- Filters & Search --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET"
                      action="{{ route('admin.support.tickets.index') }}"
                      class="row gy-2 gx-2 align-items-end">
                    {{-- Search --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold mb-1">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control form-control-sm"
                               placeholder="Subject, ticket #, customer name, email">
                    </div>

                    {{-- Status --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach(['open' => 'Open', 'pending' => 'Pending', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Priority --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold mb-1">Priority</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $key => $label)
                                <option value="{{ $key }}" @selected(request('priority') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold mb-1">Sort By</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="recent" @selected(request('sort') === 'recent')>Most recent</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="priority" @selected(request('sort') === 'priority')>Priority</option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-6 col-md-2 text-end">
                        <button type="submit" class="btn btn-teal btn-sm w-100 mb-1">
                            <i class="bi bi-funnel me-1"></i> Apply
                        </button>
                        <a href="{{ route('admin.support.tickets.index') }}"
                           class="btn btn-outline-secondary btn-sm w-100">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tickets Table --}}
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
                                <th class="small text-uppercase text-muted text-center">Replies</th>
                                <th class="small text-uppercase text-muted text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr class="{{ $ticket->is_unread_for_admin ?? false ? 'ticket-row-unread' : '' }}">
                                    <td class="align-middle">
                                        <span class="fw-semibold text-muted">
                                            #{{ $ticket->id }}
                                        </span>
                                    </td>

                                    <td class="align-middle">
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('admin.support.tickets.show', $ticket) }}"
                                               class="ticket-subject-link fw-semibold">
                                                {{ Str::limit($ticket->subject, 70) }}
                                            </a>
                                            <span class="small text-muted d-none d-md-inline">
                                                {{ Str::limit(strip_tags($ticket->latest_message_preview ?? ''), 80) }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div class="d-flex flex-column">
                                            <span class="small fw-semibold">
                                                {{ $ticket->customer->full_name ?? 'Unknown' }}
                                            </span>
                                            <span class="small text-muted">
                                                {{ $ticket->customer->email ?? '' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Priority --}}
                                    <td class="align-middle text-center">
                                        @php
                                            $priority = $ticket->priority ?? 'low';
                                        @endphp

                                        <span class="badge priority-badge priority-{{ $priority }}">
                                            {{ ucfirst($priority) }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="align-middle text-center">
                                        @php
                                            $status = $ticket->status ?? 'open';
                                        @endphp

                                        <span class="badge status-badge status-{{ $status }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>

                                    {{-- Last Update --}}
                                    <td class="align-middle small text-muted d-none d-md-table-cell">
                                        {{ optional($ticket->updated_at)->format('d M Y, H:i') }}
                                    </td>

                                    {{-- Created --}}
                                    <td class="align-middle small text-muted d-none d-lg-table-cell">
                                        {{ optional($ticket->created_at)->format('d M Y') }}
                                    </td>

                                    {{-- Replies / Unread --}}
                                    <td class="align-middle text-center">
                                        <div class="d-inline-flex flex-column align-items-center gap-1">
                                            <span class="badge bg-light text-muted small">
                                                {{ $ticket->messages_count ?? 0 }} msgs
                                            </span>
                                            @if(($ticket->unread_for_admin_count ?? 0) > 0)
                                                <span class="badge bg-danger-subtle text-danger-emphasis small">
                                                    {{ $ticket->unread_for_admin_count }} new
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="align-middle text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.support.tickets.show', $ticket) }}"
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-chat-dots me-1"></i> View
                                            </a>

                                            @if(($ticket->status ?? 'open') !== 'closed')
                                                <button type="button"
                                                        class="btn btn-outline-success ticket-quick-resolve-btn"
                                                        data-ticket-id="{{ $ticket->id }}"
                                                        data-ticket-resolve-url="{{ route('admin.support.tickets.quick-resolve', $ticket) }}">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
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

                {{-- Pagination --}}
                @if(method_exists($tickets, 'links'))
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <p class="small text-muted mb-0">
                                Showing
                                <span class="fw-semibold">{{ $tickets->firstItem() ?? 0 }}</span>
                                to
                                <span class="fw-semibold">{{ $tickets->lastItem() ?? 0 }}</span>
                                of
                                <span class="fw-semibold">{{ $tickets->total() ?? 0 }}</span>
                                tickets
                            </p>
                            <div>
                                {{ $tickets->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ secure_asset('js/admin-tickets.js') }}"></script>
@endpush
