@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<a href="{{ route('support-admin.tickets.show', $ticket) }}"
   class="support-admin-ticket-card">

    <div class="support-admin-ticket-heading">

        {{-- ============================
             RED PULSING DOT (NEW)
        ============================ --}}
        @if($ticket->status === 'waiting_for_support')
            <span class="support-admin-status-dot"></span>
        @endif

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

{{-- ============================
     PULSING DOT STYLES (NEW)
============================ --}}
<style>
.support-admin-status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ff4d4f;
    margin-right: 10px;
    display: inline-block;
    box-shadow: 0 0 0 rgba(255, 77, 79, 0.6);
    animation: adminPulse 1.3s infinite;
    align-self: center;
}

@keyframes adminPulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(255, 77, 79, 0.6);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(255, 77, 79, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(255, 77, 79, 0);
    }
}
</style>
