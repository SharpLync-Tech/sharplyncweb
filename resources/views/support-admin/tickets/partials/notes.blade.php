<div class="support-admin-side-card">
    <h3 class="support-admin-side-title">Internal notes</h3>

    <div class="support-admin-side-body support-admin-notes-list">
        @if($internalNotes->count())
            @foreach($internalNotes as $note)
                @include('support-admin.tickets.partials.note', ['note' => $note])
            @endforeach
        @else
            <p class="support-admin-empty-text">
                No internal notes yet. Use this area for technical details or escalation context.
            </p>
        @endif
    </div>

    <div class="support-admin-side-footer">
        <form method="POST"
              action="{{ route('support-admin.tickets.internal-notes.store', $ticket) }}"
              class="support-admin-form">
            @csrf
            <div class="support-admin-form-group">
                <label class="support-admin-label">Add a note</label>
                <textarea name="message"
                          rows="3"
                          class="support-admin-textarea"
                          placeholder="Add a private note for internal use..."></textarea>
            </div>
            <button type="submit" class="support-admin-btn-secondary">
                Save note
            </button>
        </form>
    </div>
</div>
