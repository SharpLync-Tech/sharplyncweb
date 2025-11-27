@extends('support-admin.layouts.base')

@section('title', 'Support Tickets')

@section('content')
    <div class="support-admin-header">
        <h1 class="support-admin-title">Support Tickets</h1>
        <a href="{{ route('admin.dashboard') }}" class="support-admin-link-back">
            Back to SharpLync Admin
        </a>
        <p class="support-admin-subtitle">
            View and manage all customer support requests. All times are shown in AEST (Brisbane time).
        </p>
    </div>

    @if(session('success'))
        <div class="support-admin-alert support-admin-alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="support-admin-filters-card">
        <form method="GET" action="{{ route('support-admin.tickets.index') }}"
              class="support-admin-filters-form">
            <div class="support-admin-filters-grid">
                <div class="support-admin-form-group">
                    <label class="support-admin-label">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="support-admin-input"
                           placeholder="Subject, reference, customer, email, phone">
                </div>

                <div class="support-admin-form-group">
                    <label class="support-admin-label">Status</label>
                    <select name="status" class="support-admin-select">
                        <option value="">All</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="support-admin-form-group">
                    <label class="support-admin-label">Priority</label>
                    <select name="priority" class="support-admin-select">
                        <option value="">All</option>
                        @foreach($priorityOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="support-admin-form-group">
                    <label class="support-admin-label">Sort</label>
                    <select name="sort" class="support-admin-select">
                        <option value="">Most recent</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                        <option value="priority" @selected(request('sort') === 'priority')>Priority</option>
                    </select>
                </div>
            </div>

            <div class="support-admin-filters-actions">
                <button type="submit" class="support-admin-btn-primary">Apply</button>
                <a href="{{ route('support-admin.tickets.index') }}" class="support-admin-btn-ghost">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($tickets->count())
        <div class="support-admin-ticket-list">
            @foreach($tickets as $ticket)
                @include('support-admin.tickets.partials.card', ['ticket' => $ticket])
            @endforeach
        </div>

        @if($tickets->hasPages())
            <div class="support-admin-pagination">
                {{ $tickets->links() }}
            </div>
        @endif
    @else
        <div class="support-admin-empty">
            <p class="support-admin-empty-title">No tickets found</p>
            <p class="support-admin-empty-text">
                Try adjusting your filters or check back later.
            </p>
        </div>
    @endif
@endsection
