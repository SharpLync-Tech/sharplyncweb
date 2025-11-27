@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<a href="{{ route('support-admin.tickets.show', $ticket) }}"
   class="support-admin-ticket-card">

    <div class="support-admin-ticket-heading">
        <div>
            @if($ticket->reference)
                <div class="support-admin-ticket-ref">
                    {{ $ticket->reference }}
                </div>
            @endif
            <h2 class="support-admin-ticket-subject">
                {{ \Illuminate\Support\Str::limit($ticket->subject, 80) }}
            </h2>
        </div>

        <div class="support-admin-ticket-status">
            <span class="support-admin-badge support-admin-badge-status-{{ $ticket->status ?? 'open' }}">
                {{ ucfirst(str_replace('_',' ', $ticket->status ?? 'open')) }}
            </span>
        </div>
    </div>

    <p class="support-admin-ticket-message">
        {{ $ticket->latest_message_preview ?? 'No message preview available.' }}
    </p>

    <div class="support-admin-ticket-meta">
        <span class="support-admin-chip support-admin-chip-{{ $ticket->priority ?? 'medium' }}">
            {{ ucfirst($ticket->priority ?? 'medium') }} priority
        </span>

        <span class="support-admin-ticket-date">
            Opened {{ optional($ticket->created_at)->format('d M Y, H:i') }}
        </span>

        @if($ticket->last_reply_at)
            <span class="support-admin-ticket-date">
                Last updated {{ \Carbon\Carbon::parse($ticket->last_reply_at)->diffForHumans() }}
            </span>
        @endif

        @if($user)
            <span class="support-admin-ticket-customer">
                {{ $profile->business_name ?? ($user->first_name . ' ' . $user->last_name) }}
                &middot; {{ $user->email }}
            </span>
        @endif

        <span class="support-admin-ticket-messages">
            {{ $ticket->messages_count ?? 0 }} messages
        </span>
    </div>
</a>
