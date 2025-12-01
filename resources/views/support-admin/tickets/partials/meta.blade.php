@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<div class="support-admin-ticket-meta-bar">
    <div class="support-admin-ticket-meta-main">
        @if($ticket->reference)
            <div class="support-admin-ticket-ref">
                {{ $ticket->reference }}
            </div>
        @endif

        <div class="support-admin-ticket-meta-chips">

            <div class="support-admin-meta-control">

                {{-- 🔴 ADD RED PULSING DOT ONLY WHEN WAITING ON SUPPORT --}}
                @if($ticket->status === 'waiting_on_support')
                    <span class="support-admin-status-dot"></span>
                @endif

                <button type="button"
                        class="support-admin-dropdown-toggle support-admin-badge support-admin-badge-status-{{ $ticket->status ?? 'open' }}"
                        data-dropdown-toggle="status">
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

@push('styles')
<style>
/* =======================
   🔴 ADMIN RED PULSE DOT
 ======================= */
.support-admin-status-dot {
    width: 10px;
    height: 10px;
    background: #ff3b3b;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    position: relative;
    top: 2px;
    box-shadow: 0 0 0 rgba(255, 59, 59, 0.6);
    animation: supportAdminPulse 1.3s infinite ease-out;
}

@keyframes supportAdminPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 59, 59, 0.5);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(255, 59, 59, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 59, 59, 0);
    }
}
</style>
@endpush
