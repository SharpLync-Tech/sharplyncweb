<div class="ticket-note-item mb-2">
    <div class="small fw-semibold">
        {{ $note->admin?->name ?? 'Staff member' }}
        <span class="text-muted">
            · {{ optional($note->created_at)->format('d M Y, H:i') }}
        </span>
    </div>
    <p class="small mb-0">{{ $note->message }}</p>
</div>
