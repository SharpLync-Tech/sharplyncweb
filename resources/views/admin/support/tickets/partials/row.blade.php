@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<tr>
    <td class="align-middle">
        <span class="fw-semibold text-muted">#{{ $ticket->id }}</span><br>
        @if($ticket->reference)
            <span class="badge bg-light text-muted small">{{ $ticket->reference }}</span>
        @endif
    </td>

    <td class="align-middle">
        <a href="{{ route('admin.support.tickets.show', $ticket) }}"
           class="ticket-subject-link fw-semibold">
            {{ \Illuminate\Support\Str::limit($ticket->subject, 70) }}
        </a>
        @if($ticket->latest_message_preview)
            <div class="small text-muted">
                {{ \Illuminate\Support\Str::limit($ticket->latest_message_preview, 90) }}
            </div>
        @endif
    </td>

    <td class="align-middle">
        <div class="d-flex flex-column">
            <span class="small fw-semibold">
                {{ $profile->business_name ?? ($user ? $user->first_name . ' ' . $user->last_name : 'Customer') }}
            </span>
            @if($user)
                <span class="small text-muted">{{ $user->email }}</span>
            @endif
            @if($profile?->mobile_number)
                <span class="small text-muted">{{ $profile->mobile_number }}</span>
            @endif
        </div>
    </td>

    {{-- Priority --}}
    <td class="align-middle text-center">
        <span class="badge priority-badge priority-{{ $ticket->priority ?? 'medium' }}">
            {{ ucfirst($ticket->priority ?? 'medium') }}
        </span>
    </td>

    {{-- Status --}}
    <td class="align-middle text-center">
        <span class="badge status-badge status-{{ $ticket->status ?? 'open' }}">
            {{ ucfirst($ticket->status ?? 'open') }}
        </span>
    </td>

    {{-- Last update --}}
    <td class="align-middle small text-muted d-none d-md-table-cell">
        {{ optional($ticket->updated_at)->format('d M Y, H:i') }}
    </td>

    {{-- Created --}}
    <td class="align-middle small text-muted d-none d-lg-table-cell">
        {{ optional($ticket->created_at)->format('d M Y') }}
    </td>

    {{-- Messages count --}}
    <td class="align-middle text-center">
        <span class="badge bg-light text-muted small">
            {{ $ticket->messages_count ?? 0 }} msgs
        </span>
    </td>

    {{-- Actions --}}
    <td class="align-middle text-end">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.support.tickets.show', $ticket) }}"
               class="btn btn-outline-primary">
                <i class="bi bi-chat-dots me-1"></i> View
            </a>

            @if(($ticket->status ?? 'open') !== 'closed')
                <button type="button"
                        class="btn btn-outline-success js-ticket-quick-resolve"
                        data-ticket-id="{{ $ticket->id }}"
                        data-resolve-url="{{ route('admin.support.tickets.quick-resolve', $ticket) }}">
                    <i class="bi bi-check2-circle"></i>
                </button>
            @endif
        </div>
    </td>
</tr>
