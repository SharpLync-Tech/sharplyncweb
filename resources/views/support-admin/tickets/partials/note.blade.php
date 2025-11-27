<div class="support-admin-note">
    <div class="support-admin-note-header">
        <span class="support-admin-note-author">
            {{ $note->admin?->name ?? 'Staff member' }}
        </span>
        <span class="support-admin-note-time">
            {{ optional($note->created_at)->format('d M Y, H:i') }}
        </span>
    </div>
    <div class="support-admin-note-body">
        {{ $note->message }}
    </div>
</div>
