@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<div class="support-admin-ticket-meta-bar">
    <div class="support-admin-ticket-meta-main">

        {{-- =========================================
             PULSING DOT (NEW)
        ========================================== --}}
        @if($ticket->status === 'waiting_for_support')
            <span class="support-admin-status-dot"></span>
        @endif

        @if($ticket->reference)
            <div class="support-admin-ticket-ref">
                {{ $ticket->reference }}
            </div>
        @endif

        <div class="support-admin-ticket-meta-chips">
            <div class="support-admin-meta-control">
                <button type="button"
                        class="support-admin-dropdown-toggle support-admin-badge support-admin-badge-status-{{ $ticket->status ?? 'open' }}"
                        data-dropdown-toggle="status"
                        style="display:flex; align-items:center; gap:8px;">

                    {{-- 🔴 Pulsing red dot (only for waiting_for_support) --}}
                    @if($ticket->status === 'waiting_for_support')
                        <span class="admin-status-dot"></span>
                    @endif

                    {{ strtoupper(str_replace('_',' ', $ticket->status ?? 'open')) }}
                </button>


                <div class="support-admin-dropdown" data-dropdown-panel="status">
                    <div class="support-admin-dropdown-header">Change status</div>
                    <ul class="support-admin-dropdown-list">
                        @foreach($statusOptions as $value => $label)
                            <li>
                                <form method="POST"
                                      action="{{ route('support-admin.tickets.update-status', $ticket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $value }}">
                                    <button type="submit"
                                            class="support-admin-dropdown-option @if($ticket->status === $value) is-active @endif">
                                        {{ $label }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="support-admin-meta-control">
                <button type="button"
                        class="support-admin-dropdown-toggle support-admin-chip support-admin-chip-{{ $ticket->priority ?? 'medium' }}"
                        data-dropdown-toggle="priority">
                    {{ ucfirst($ticket->priority ?? 'medium') }} priority
                </button>

                <div class="support-admin-dropdown" data-dropdown-panel="priority">
                    <div class="support-admin-dropdown-header">Change priority</div>
                    <ul class="support-admin-dropdown-list">
                        @foreach($priorityOptions as $value => $label)
                            <li>
                                <form method="POST"
                                      action="{{ route('support-admin.tickets.update-priority', $ticket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="priority" value="{{ $value }}">
                                    <button type="submit"
                                            class="support-admin-dropdown-option @if($ticket->priority === $value) is-active @endif">
                                        {{ $label }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <span class="support-admin-ticket-meta-text">
                Opened {{ optional($ticket->created_at)->format('d M Y, H:i') }}
            </span>

            @if($ticket->last_reply_at)
                <span class="support-admin-ticket-meta-text">
                    Last updated {{ \Carbon\Carbon::parse($ticket->last_reply_at)->diffForHumans() }}
                </span>
            @endif
        </div>
    </div>

    <div class="support-admin-ticket-meta-actions">
        @if($ticket->status !== 'closed')
            <form method="POST"
                  action="{{ route('support-admin.tickets.quick-resolve', $ticket) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="support-admin-btn-secondary">
                    Mark as resolved
                </button>
            </form>
        @endif
    </div>
</div>

{{-- PULSING DOT CSS --}}
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
}

@keyframes adminPulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(255, 77, 79, 0.6);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 8px rgba(255, 77, 79, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(255, 77, 79, 0);
    }
}
</style>
