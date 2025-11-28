{{-- resources/views/customers/support/show.blade.php --}}
{{-- SharpLync Support Module — Customer Ticket View (Quill-enabled) --}}

@extends('customers.layouts.customer-layout')

@section('title', 'Ticket ' . $ticket->reference)

@push('styles')
    {{-- Local Quill CSS --}}
    <link href="{{ secure_asset('quill/quill.core.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('quill/quill.snow.css') }}" rel="stylesheet">

    {{-- Emoji plugin --}}
    <link href="{{ secure_asset('quill/quill-emoji.css') }}" rel="stylesheet">

    {{-- Support styling --}}
    <link rel="stylesheet" href="{{ secure_asset('css/support/support.css') }}">
@endpush

@section('content')
<div class="support-wrapper">

    {{-- HEADER --}}
    <div class="support-header">
        <h1 class="support-title">{{ $ticket->subject }}</h1>

        <a href="{{ route('customer.support.index') }}" class="support-btn-outline support-back-btn">
            Back to my tickets
        </a>

        <p class="support-timezone-note">All times shown in AEST (Brisbane time).</p>

        <p class="support-subtitle">
            Support Reference: <strong>{{ $ticket->reference }}</strong>
        </p>
    </div>

    {{-- ALERTS --}}
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


    {{-- META BAR --}}
    <div class="support-ticket-meta-bar">

        {{-- Status --}}
        <div class="support-meta-control">
            @if ($ticket->status === 'waiting_on_customer')
                <div class="support-status-wrapper">
                    <span class="support-status-dot"></span>                    
                </div>
            @endif
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
                            <button type="button"
                                    class="support-dropdown-option {{ $ticket->status === $value ? 'is-active' : '' }}"
                                    data-value="{{ $value }}">
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Priority --}}
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
                            <button type="button"
                                    class="support-dropdown-option {{ $ticket->priority === $value ? 'is-active' : '' }}"
                                    data-value="{{ $value }}">
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


    {{-- =====================================
         REPLY BOX (ONLY IF NOT CLOSED)
    ====================================== --}}
    @if($ticket->status !== 'closed' && $ticket->status !== 'resolved')
        <div class="support-reply-box">
            <h2 class="support-reply-title">Add a reply</h2>

            <form action="{{ route('customer.support.tickets.reply.store', $ticket) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="support-form">
                @csrf

                <div class="support-form-group">
                    <label class="support-label">Your message</label>

                    {{-- Toolbar --}}
                    <div id="quill-toolbar" class="quill-toolbar">
                        <span class="ql-formats">
                            <button class="ql-bold"></button>
                            <button class="ql-italic"></button>
                            <button class="ql-underline"></button>
                        </span>

                        <span class="ql-formats">
                            <button class="ql-list" value="bullet"></button>
                        </span>

                        <span class="ql-formats">
                            <button class="ql-emoji"></button>
                        </span>

                        <span class="ql-formats attach-btn">
                            <label>
                                📤
                                <input type="file" name="attachment" hidden>
                            </label>
                        </span>
                    </div>

                    {{-- Editor --}}
                    <div id="quill-editor" class="quill-editor"></div>

                    {{-- Hidden field for HTML --}}
                    <input type="hidden" name="message" id="quill-html">
                </div>

                <div class="support-form-actions">
                    <button type="submit" class="support-btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
    @else
        <div class="support-ticket-closed-note">
            This ticket is marked as <strong>{{ ucfirst($ticket->status) }}</strong>.
            If you need further help, please open a new support request.
        </div>
    @endif

    {{-- ===========================
        MESSAGES DISPLAY
    ============================ --}}
    @php
        $allRepliesDesc = $ticket->replies->sortByDesc('created_at')->values();
        $latestReplies  = $allRepliesDesc->take(2);

        $olderReplies   = $ticket->replies
            ->filter(fn($reply) => !$latestReplies->contains('id', $reply->id))
            ->sortBy('created_at')
            ->values();
    @endphp

    <div class="support-ticket-thread">

        {{-- NEWEST 2 --}}
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
                    {!! $reply->message !!}

                    @if($reply->attachment_path)
                        <div class="support-attachment-file">
                            📤 <a href="{{ route('customer.support.attachment.download', $reply->id) }}">
                                {{ $reply->attachment_original_name }}
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        @endforeach


        {{-- ORIGINAL TICKET + OLDER REPLIES --}}
        @if($ticket->message || $olderReplies->isNotEmpty())
            <div class="support-older-wrapper">

                <button type="button"
                        class="support-older-toggle"
                        data-support-older-toggle>
                    View earlier conversation(s)
                </button>

                <div class="support-older-container" data-support-older-container hidden>

                    {{-- Original ticket --}}
                    @if ($ticket->message)
                        <div class="support-message support-message-customer">
                            <div class="support-message-header">
                                <span class="support-message-author">{{ $customer->name ?? 'You' }}</span>

                                <span class="support-message-time">
                                    {{ $ticket->created_at->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
                                </span>
                            </div>

                            <div class="support-message-body">
                                {!! $ticket->message !!}
                            </div>
                        </div>
                    @endif

                    {{-- Older replies --}}
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
                                {!! $reply->message !!}
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
    {{-- Quill --}}
    <script src="{{ secure_asset('quill/quill.min.js') }}"></script>

    {{-- Emoji plugin --}}
    <script src="{{ secure_asset('quill/quill-emoji.js') }}"></script>

    {{-- UI logic --}}
    <script src="{{ secure_asset('js/support/support.js') }}"></script>

    {{-- Quill initialiser --}}
    <script src="{{ secure_asset('js/support/support-quill.js') }}"></script>
@endpush
