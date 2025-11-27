@if(($ticket->status ?? 'open') !== 'closed')
    <div class="card shadow-sm admin-ticket-reply-card mt-3">
        <div class="card-header bg-white border-0 pb-0">
            <h6 class="mb-1 fw-semibold">Reply to customer</h6>
            <p class="text-muted small mb-0">
                Your reply will be emailed to the customer (once mail is wired up) and logged here.
            </p>
        </div>
        <div class="card-body">
            <form method="POST"
                  action="{{ route('admin.support.tickets.reply', $ticket) }}"
                  class="ticket-reply-form">
                @csrf

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Message</label>
                    <textarea name="message"
                              rows="4"
                              class="form-control"
                              placeholder="Type your reply here...">{{ old('message') }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="alert alert-secondary mt-3 d-flex align-items-center gap-2">
        <i class="bi bi-lock-fill"></i>
        <div>
            <strong>This ticket is closed.</strong>
            <span class="d-block small">
                Change the status back to Open or Pending if you need to reply again.
            </span>
        </div>
    </div>
@endif
