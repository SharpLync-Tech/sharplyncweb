@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
    <div class="d-flex flex-column">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="mb-0 fw-semibold text-sharplync-navy">
                Ticket #{{ $ticket->id }} — {{ $ticket->subject }}
            </h2>
            <span class="badge status-badge status-{{ $ticket->status ?? 'open' }}">
                {{ ucfirst($ticket->status ?? 'open') }}
            </span>
        </div>
        <p class="text-muted small mb-0">
            Created {{ optional($ticket->created_at)->format('d M Y, H:i') }}
            @if($ticket->last_reply_at)
                · Last reply {{ \Carbon\Carbon::parse($ticket->last_reply_at)->format('d M Y, H:i') }}
            @endif
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        @if(($ticket->status ?? 'open') !== 'closed')
            <form method="POST"
                  action="{{ route('admin.support.tickets.update-status', $ticket) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="resolved">
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-check2-circle me-1"></i> Mark as Resolved
                </button>
            </form>

            <form method="POST"
                  action="{{ route('admin.support.tickets.update-status', $ticket) }}">
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
