<div class="card shadow-sm admin-ticket-notes-card">
    <div class="card-header bg-white border-0 pb-0">
        <h6 class="mb-1 fw-semibold">Internal Notes</h6>
        <p class="text-muted small mb-0">
            Only visible to SharpLync staff. Not sent to customer.
        </p>
    </div>
    <div class="card-body">
        @if($internalNotes->count())
            <div class="ticket-internal-notes-list mb-3">
                @foreach($internalNotes as $note)
                    @include('admin.support.tickets.partials.note', ['note' => $note])
                @endforeach
            </div>
        @else
            <p class="text-muted small mb-3">
                No internal notes yet. Use this for technical details, escalation context, or history.
            </p>
        @endif

        <form method="POST"
              action="{{ route('admin.support.tickets.internal-notes.store', $ticket) }}">
            @csrf
            <div class="mb-2">
                <textarea name="message"
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
