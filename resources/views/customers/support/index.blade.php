{{-- resources/views/customers/support/index.blade.php --}}
{{-- SharpLync Support Module V1: Ticket list for customer --}}

@extends('customers.layouts.customer-layout')

@section('title', 'My Support Tickets')

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/support/support.css') }}">
@endpush

@section('content')
<div class="support-wrapper">
    <div class="support-header">
        <h1 class="support-title">Support</h1>
        <p class="support-subtitle">View and manage your support requests.</p>
        <a href="{{ route('customer.support.tickets.create') }}" class="support-btn-primary">
            + New Support Request
        </a>
    </div>

    @if (session('success'))
        <div class="support-alert support-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($tickets->isEmpty())
        <div class="support-empty">
            <p>You don’t have any support tickets yet.</p>
            <a href="{{ route('customer.support.tickets.create') }}" class="support-btn-secondary">
                Create your first support ticket
            </a>
        </div>
    @else
        <div class="support-ticket-list">
            @foreach ($tickets as $ticket)
                <a href="{{ route('customer.support.tickets.show', $ticket) }}" class="support-ticket-card">
                    <div class="support-ticket-main">
                        <div class="support-ticket-heading">
                            <span class="support-ticket-ref">{{ $ticket->reference }}</span>
                            <span class="support-badge support-badge-{{ $ticket->status }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <h2 class="support-ticket-subject">{{ $ticket->subject }}</h2>
                        <p class="support-ticket-message">
                            {{ \Illuminate\Support\Str::limit($ticket->message, 120) }}
                        </p>
                    </div>
                    <div class="support-ticket-meta">
                        <span class="support-chip support-chip-{{ $ticket->priority }}">
                            {{ ucfirst($ticket->priority) }} priority
                        </span>
                        <span class="support-ticket-date">
                            Opened {{ $ticket->created_at->format('d M Y, H:i') }}
                        </span>
                        @if($ticket->last_reply_at)
                            <span class="support-ticket-date">
                                Last updated {{ $ticket->last_reply_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="support-pagination">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ secure_asset('js/support/support.js') }}"></script>
@endpush
