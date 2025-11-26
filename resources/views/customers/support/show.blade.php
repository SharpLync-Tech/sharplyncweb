{{-- resources/views/customers/support/show.blade.php --}}
{{-- SharpLync Support Module V1: Ticket detail + replies --}}

@extends('customers.layouts.customer-layout')

@section('title', 'Ticket ' . $ticket->reference)

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/support/support.css') }}">
@endpush

@section('content')
<div class="support-wrapper">
    <div class="support-header">
        <h1 class="support-title">
            {{ $ticket->subject }}
        </h1>
        <p class="support-subtitle">
            Reference: <strong>{{ $ticket->reference }}</strong>
            <p class="support-timezone-note">All times shown in AEST (Brisbane time).</p>
        </p>
        <a href="{{ route('customer.support.index') }}" class="support-link-back">
            Back to my tickets
        </a>
    </div>

    @if (session('success'))
        <div class="support-alert support-alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="support-ticket-meta-bar">
        <span class="support-badge support-badge-{{ $ticket->status }}">
            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
        </span>
        <span class="support-chip support-chip-{{ $ticket->priority }}">
            {{ ucfirst($ticket->priority) }} priority
        </span>
        <span class="support-ticket-date">
            Opened {{ $ticket->created_at->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
        </span>
        @if($ticket->last_reply_at)
            <span class="support-ticket-date">
                Last updated {{ $ticket->last_reply_at->diffForHumans() }}
            </span>
        @endif
    </div>

    <div class="support-ticket-thread">
        <div class="support-message support-message-customer">
            <div class="support-message-header">
                <span class="support-message-author">
                    {{ $customer->name ?? 'You' }}
                </span>
                <span class="support-message-time">
                    {{ $ticket->created_at->format('d M Y, H:i') }}
                </span>
            </div>
            <div class="support-message-body">
                {!! nl2br(e($ticket->message)) !!}
            </div>
        </div>

        @foreach ($ticket->replies as $reply)
            <div class="support-message {{ $reply->admin_id ? 'support-message-staff' : 'support-message-customer' }}">
                <div class="support-message-header">
                    <span class="support-message-author">
                        {{ $reply->admin_id ? 'SharpLync Support' : ($customer->name ?? 'You') }}
                    </span>
                    <span class="support-message-time">
                        {{ $reply->created_at->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
                    </span>
                </div>
                <div class="support-message-body">
                    {!! nl2br(e($reply->message)) !!}
                </div>
            </div>
        @endforeach
    </div>

    @if ($errors->any())
        <div class="support-alert support-alert-error">
            <strong>Please check your reply:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($ticket->status !== 'closed' && $ticket->status !== 'resolved')
        <div class="support-reply-box">
            <h2 class="support-reply-title">Add a reply</h2>
            <form action="{{ route('customer.support.tickets.reply.store', $ticket) }}"
                  method="POST"
                  class="support-form">
                @csrf

                <div class="support-form-group">
                    <label for="message" class="support-label">Your message</label>
                    <textarea id="message"
                              name="message"
                              class="support-textarea"
                              rows="5"
                              required>{{ old('message') }}</textarea>
                </div>

                <div class="support-form-actions">
                    <button type="submit" class="support-btn-primary">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="support-ticket-closed-note">
            This ticket is marked as <strong>{{ ucfirst($ticket->status) }}</strong>.
            If you need further help on this issue, please open a new support request.
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ secure_asset('js/support/support.js') }}"></script>
@endpush
