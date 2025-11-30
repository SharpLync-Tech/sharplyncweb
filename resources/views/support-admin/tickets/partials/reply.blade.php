@if($ticket->status !== 'closed')
    <div class="support-admin-reply-card">
        <h2 class="support-admin-reply-title">Add a reply</h2>

        <form method="POST"
              action="{{ route('support-admin.tickets.reply', $ticket) }}"
              class="support-admin-form">
            @csrf

            <div class="support-admin-form-group">
                <label class="support-admin-label">Your message</label>
                <textarea name="message"
                          rows="4"
                          class="support-admin-textarea"
                          placeholder="Type your reply to the customer...">{{ old('message') }}</textarea>
            </div>

            <div class="support-admin-form-actions">
                <button type="submit" class="support-admin-btn-primary">
                    Send reply
                </button>
            </div>
        </form>
    </div>
@else
    <div class="support-admin-closed-note">
        <strong>This ticket is closed.</strong>
        <span class="support-admin-closed-text">
            Change the status above if you need to reopen and reply again.
        </span>
    </div>
@endif
