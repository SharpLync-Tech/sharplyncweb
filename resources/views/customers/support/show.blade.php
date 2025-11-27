{{-- resources/views/customers/support/show.blade.php --}}
{{-- SharpLync Support Module V1: Ticket detail + replies (upgraded view) --}}

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

        <a href="{{ route('customer.support.index') }}" class="support-btn-outline support-back-btn">
            Back to my tickets
        </a>


        <p class="support-timezone-note">
            All times shown in AEST (Brisbane time).
        </p>
        <p class="support-subtitle">
            Support Reference: <strong>{{ $ticket->reference }}</strong>
        </p>
    </div>

    @if (session('success'))
        <div class="support-alert support-alert-success">
            {{ session('success') }}
        </div>
    @endif

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

    <div class="support-ticket-meta-bar">

        {{-- Status pill with dropdown --}}
        <div class="support-meta-control">
            <button
                type="button"
                class="support-badge support-badge-{{ $ticket->status }} support-dropdown-toggle"
                data-type="status"
                data-current="{{ $ticket->status }}"
                data-update-url="{{ route('customer.support.tickets.status.update', $ticket) }}"
            >
                {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
            </button>

            <div class="support-dropdown" data-dropdown-panel="ticket-status">
                <div class="support-dropdown-header">Change status</div>
                <ul class="support-dropdown-list">
                    @php
                        $statusOptions = [
                            'open'     => 'Open',
                            'resolved' => 'Resolved',
                            'closed'   => 'Closed',
                        ];
                    @endphp
                    @foreach ($statusOptions as $value => $label)
                        <li>
                            <button
                                type="button"
                                class="support-dropdown-option {{ $ticket->status === $value ? 'is-active' : '' }}"
                                data-value="{{ $value }}"
                            >
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Priority pill with dropdown --}}
        <div class="support-meta-control">
            <button
                type="button"
                class="support-chip support-chip-{{ $ticket->priority }} support-dropdown-toggle"
                data-type="priority"
                data-current="{{ $ticket->priority }}"
                data-update-url="{{ route('customer.support.tickets.priority.update', $ticket) }}"
            >
                {{ ucfirst($ticket->priority) }} priority
            </button>

            <div class="support-dropdown" data-dropdown-panel="ticket-priority">
                <div class="support-dropdown-header">Change priority</div>
                <ul class="support-dropdown-list">
                    @php
                        $priorityOptions = [
                            'low'    => 'Low',
                            'medium' => 'Medium',
                            'high'   => 'High',
                            'urgent' => 'Urgent',
                        ];
                    @endphp
                    @foreach ($priorityOptions as $value => $label)
                        <li>
                            <button
                                type="button"
                                class="support-dropdown-option {{ $ticket->priority === $value ? 'is-active' : '' }}"
                                data-value="{{ $value }}"
                            >
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <span class="support-ticket-date">
            Opened {{ $ticket->created_at->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
        </span>

        @if($ticket->last_reply_at)
            <span class="support-ticket-date">
                Last updated {{ $ticket->last_reply_at->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
            </span>
        @endif
    </div>

    {{-- Reply box at the top if ticket is not closed/resolved --}}
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

    @php
        // Sort all replies newest → oldest
        $allRepliesDesc = $ticket->replies->sortByDesc('created_at')->values();

        // Take the newest 2 replies for the visible area
        $latestReplies = $allRepliesDesc->take(2);

        // Older replies = everything else, shown in "earlier conversation(s)"
        $olderReplies = $ticket->replies
            ->filter(fn($reply) => !$latestReplies->contains('id', $reply->id))
            ->sortBy('created_at')
            ->values();
    @endphp

    <div class="support-ticket-thread">

        {{-- Latest messages (newest at the top) --}}
        @foreach ($latestReplies as $reply)
            <div class="support-message {{ $reply->isAdmin() ? 'support-message-staff' : 'support-message-customer' }}">
                <div class="support-message-header">
                    <span class="support-message-author">
                        @if ($reply->isCustomer())
                            You
                        @elseif ($reply->isAdmin())
                            SharpLync Support
                        @else
                            Unknown
                        @endif
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

        {{-- Collapsible earlier conversation (original ticket + older replies) --}}
        @if($ticket->message || $olderReplies->isNotEmpty())
            <div class="support-older-wrapper">
                <button type="button"
                        class="support-older-toggle"
                        data-support-older-toggle>
                    View earlier conversation(s)
                </button>

                <div class="support-older-container" data-support-older-container hidden>

                    {{-- Original ticket message (always oldest) --}}
                    @if ($ticket->message)
                        <div class="support-message support-message-customer">
                            <div class="support-message-header">
                                <span class="support-message-author">
                                    {{ $customer->name ?? 'You' }}
                                </span>
                                <span class="support-message-time">
                                    {{ $ticket->created_at->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
                                </span>
                            </div>
                            <div class="support-message-body">
                                {!! nl2br(e($ticket->message)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Older replies in chronological order --}}
                    @foreach ($olderReplies as $reply)
                        <div class="support-message {{ $reply->isAdmin() ? 'support-message-staff' : 'support-message-customer' }}">
                            <div class="support-message-header">
                                <span class="support-message-author">
                                    @if ($reply->isCustomer())
                                        You
                                    @elseif ($reply->isAdmin())
                                        SharpLync Support
                                    @else
                                        Unknown
                                    @endif
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
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ secure_asset('js/support/support.js') }}"></script>
@endpush
