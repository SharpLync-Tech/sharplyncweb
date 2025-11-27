{{-- 
  Page: resources/views/admin/support/tickets/show.blade.php
  Version: v1.0 (Phase 1)
  Description:
  - Admin Ticket Detail & Conversation
  - Conversation thread + reply box
  - Status & priority controls
  - Internal notes tab (Phase 1 UI only)
--}}

@extends('admin.layouts.admin-layout')

@section('title', 'Ticket #'.$ticket->id.' — '.$ticket->subject)

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/admin-tickets.css') }}">
@endpush

@section('content')
    <div class="container-fluid mt-4 admin-ticket-show">

        {{-- Breadcrumb / Back --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h2 class="mb-0 fw-semibold">
                        #{{ $ticket->id }} &mdash; {{ $ticket->subject }}
                    </h2>
                    @php $status = $ticket->status ?? 'open'; @endphp
                    <span class="badge status-badge status-{{ $status }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>
                <p class="text-muted small mb-0">
                    Created {{ optional($ticket->created_at)->format('d M Y, H:i') }}
                    &middot;
                    Last updated {{ optional($ticket->updated_at)->format('d M Y, H:i') }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @if(($ticket->status ?? 'open') !== 'closed')
                    <form method="POST"
                          action="{{ route('admin.support.tickets.update-status', $ticket) }}"
                          class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-check2-circle me-1"></i> Mark as Resolved
                        </button>
                    </form>
                @endif

                @if(($ticket->status ?? 'open') !== 'closed')
                    <form method="POST"
                          action="{{ route('admin.support.tickets.update-status', $ticket) }}"
                          class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Close Ticket
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-3">
            {{-- Left: Ticket meta --}}
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="mb-1 fw-semibold">Ticket Details</h6>
                        <p class="text-muted small mb-0">
                            Quick overview of the ticket and customer.
                        </p>
                    </div>
                    <div class="card-body">
                        {{-- Customer --}}
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small text-muted text-uppercase">Customer</span>
                            </div>
                            <p class="mb-0 fw-semibold">
                                {{ $ticket->customer->full_name ?? 'Unknown customer' }}
                            </p>
                            <p class="mb-0 small text-muted">
                                {{ $ticket->customer->email ?? '' }}
                            </p>
                        </div>

                        {{-- Priority --}}
                        <div class="mb-3">
                            <span class="small text-muted text-uppercase d-block mb-1">Priority</span>
                            <form method="POST"
                                  action="{{ route('admin.support.tickets.update-priority', $ticket) }}"
                                  class="d-flex align-items-center gap-2 flex-wrap">
                                @csrf
                                @method('PATCH')
                                @php $priority = $ticket->priority ?? 'low'; @endphp
                                <span class="badge priority-badge priority-{{ $priority }}">
                                    {{ ucfirst($priority) }}
                                </span>
                                <select name="priority"
                                        class="form-select form-select-sm w-auto">
                                    @foreach(['low', 'medium', 'high'] as $p)
                                        <option value="{{ $p }}" @selected($priority === $p)>{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary btn-sm">
                                    Update
                                </button>
                            </form>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <span class="small text-muted text-uppercase d-block mb-1">Status</span>
                            <form method="POST"
                                  action="{{ route('admin.support.tickets.update-status', $ticket) }}"
                                  class="d-flex align-items-center gap-2 flex-wrap">
                                @csrf
                                @method('PATCH')
                                <select name="status"
                                        class="form-select form-select-sm w-auto">
                                    @foreach(['open', 'pending', 'resolved', 'closed'] as $s)
                                        <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary btn-sm">
                                    Apply
                                </button>
                            </form>
                        </div>

                        {{-- Ticket meta --}}
                        <div class="mb-3">
                            <span class="small text-muted text-uppercase d-block mb-1">Meta</span>
                            <dl class="small mb-0">
                                <div class="d-flex justify-content-between">
                                    <dt class="text-muted mb-1">Ticket ID</dt>
                                    <dd class="mb-1">#{{ $ticket->id }}</dd>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <dt class="text-muted mb-1">Created</dt>
                                    <dd class="mb-1">
                                        {{ optional($ticket->created_at)->format('d M Y, H:i') }}
                                    </dd>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <dt class="text-muted mb-1">Updated</dt>
                                    <dd class="mb-1">
                                        {{ optional($ticket->updated_at)->format('d M Y, H:i') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Internal notes (UI only, wiring optional in Phase 1) --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0 pb-0 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 fw-semibold">Internal Notes</h6>
                            <p class="text-muted small mb-0">
                                Visible only to SharpLync staff.
                            </p>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($internalNotes) && count($internalNotes))
                            <div class="ticket-internal-notes-list mb-3">
                                @foreach($internalNotes as $note)
                                    <div class="ticket-note-item mb-2">
                                        <div class="small fw-semibold">
                                            {{ $note->author_name ?? 'Staff member' }}
                                            <span class="text-muted">
                                                &middot; {{ optional($note->created_at)->format('d M Y, H:i') }}
                                            </span>
                                        </div>
                                        <p class="small mb-0">{{ $note->body }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small mb-3">
                                No internal notes yet. Use this for troubleshooting, context or escalation notes.
                            </p>
                        @endif

                        <form method="POST"
                              action="{{ route('admin.support.tickets.internal-notes.store', $ticket) }}">
                            @csrf
                            <div class="mb-2">
                                <textarea name="body"
                                          rows="3"
                                          class="form-control form-control-sm"
                                          placeholder="Add an internal note..."></textarea>
                            </div>
                            <button class="btn btn-primary btn-sm">
                                <i class="bi bi-journal-text me-1"></i> Add Note
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right: Conversation --}}
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-semibold">Conversation</h6>
                                <p class="text-muted small mb-0">
                                    Chronological view of all messages between customer and support.
                                </p>
                            </div>
                            <div class="small text-muted">
                                {{ $ticket->messages_count ?? count($messages ?? []) }} messages
                            </div>
                        </div>
                    </div>

                    <div class="card-body ticket-conversation-wrapper">
                        @php
                            $messagesCollection = $messages ?? $ticket->messages ?? collect();
                        @endphp

                        @forelse($messagesCollection as $message)
                            @php
                                $isCustomer = ($message->sender_type ?? 'customer') === 'customer';
                                $bubbleClass = $isCustomer ? 'ticket-bubble-customer' : 'ticket-bubble-admin';
                                $avatarLetter = $isCustomer
                                    ? strtoupper(substr($ticket->customer->full_name ?? 'C', 0, 1))
                                    : strtoupper(substr($message->sender_name ?? 'S', 0, 1));
                            @endphp

                            <div class="ticket-message-row {{ $isCustomer ? 'justify-content-start' : 'justify-content-end' }}">
                                {{-- Avatar --}}
                                <div class="ticket-avatar d-none d-sm-flex">
                                    <span class="{{ $isCustomer ? 'ticket-avatar-customer' : 'ticket-avatar-admin' }}">
                                        {{ $avatarLetter }}
                                    </span>
                                </div>

                                {{-- Bubble --}}
                                <div class="ticket-message-bubble {{ $bubbleClass }}">
                                    <div class="ticket-message-header">
                                        <div class="ticket-message-author">
                                            @if($isCustomer)
                                                {{ $ticket->customer->full_name ?? 'Customer' }}
                                                <span class="badge rounded-pill bg-light text-muted ms-1">
                                                    Customer
                                                </span>
                                            @else
                                                {{ $message->sender_name ?? 'SharpLync Support' }}
                                                <span class="badge rounded-pill bg-primary-subtle text-primary ms-1">
                                                    Support
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ticket-message-meta">
                                            <span class="small text-muted">
                                                {{ optional($message->created_at)->format('d M Y, H:i') }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ticket-message-body">
                                        {!! nl2br(e($message->body)) !!}
                                    </div>

                                    @if($message->attachments && count($message->attachments))
                                        <div class="ticket-message-attachments mt-2">
                                            <span class="small text-muted d-block mb-1">Attachments</span>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($message->attachments as $attachment)
                                                    <a href="{{ $attachment->url }}"
                                                       target="_blank"
                                                       class="badge bg-light text-muted text-decoration-none">
                                                        <i class="bi bi-paperclip me-1"></i>
                                                        {{ $attachment->filename }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-chat-square-dots display-6 d-block mb-2"></i>
                                <p class="mb-1 fw-semibold">No messages yet</p>
                                <p class="small mb-0">
                                    Use the reply box below to start the conversation.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Reply Box --}}
                @if(($ticket->status ?? 'open') !== 'closed')
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="mb-1 fw-semibold">Reply to customer</h6>
                            <p class="text-muted small mb-0">
                                Your reply will be emailed to the customer and added to the conversation.
                            </p>
                        </div>
                        <div class="card-body">
                            <form method="POST"
                                  action="{{ route('admin.support.tickets.reply', $ticket) }}"
                                  enctype="multipart/form-data"
                                  class="ticket-reply-form">
                                @csrf

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Message</label>
                                    <textarea name="body"
                                              rows="4"
                                              class="form-control"
                                              placeholder="Type your reply here..."></textarea>
                                </div>

                                <div class="row gy-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Attachments</label>
                                        <input type="file"
                                               name="attachments[]"
                                               class="form-control form-control-sm"
                                               multiple>
                                        <p class="small text-muted mb-0">
                                            Optional. You can attach screenshots, PDFs, or other files.
                                        </p>
                                    </div>

                                    <div class="col-12 col-md-6 d-flex flex-column justify-content-end">
                                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-2 mt-md-0">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-send me-1"></i> Send Reply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-lock-fill"></i>
                        <div>
                            <strong>This ticket is closed.</strong>
                            <span class="d-block small">
                                Reopen the ticket via the status control if you need to send another reply.
                            </span>
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
