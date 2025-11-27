@php
    $isCustomer = $isCustomer ?? false;
    $authorName = $authorName ?? 'User';
    $timestamp  = $timestamp ?? now();
    $label      = $label ?? ($isCustomer ? 'Customer' : 'Support');
    $avatarLetter = strtoupper(mb_substr($authorName, 0, 1));
@endphp

<div class="ticket-message-row {{ $isCustomer ? 'justify-content-start' : 'justify-content-end' }}">
    {{-- Avatar --}}
    <div class="ticket-avatar d-none d-sm-flex">
        <span class="{{ $isCustomer ? 'ticket-avatar-customer' : 'ticket-avatar-admin' }}">
            {{ $avatarLetter }}
        </span>
    </div>

    {{-- Bubble --}}
    <div class="ticket-message-bubble {{ $isCustomer ? 'ticket-bubble-customer' : 'ticket-bubble-admin' }}">
        <div class="ticket-message-header">
            <div class="ticket-message-author">
                {{ $authorName }}
                <span class="badge rounded-pill {{ $isCustomer ? 'bg-light text-muted' : 'bg-primary-subtle text-primary' }} ms-1">
                    {{ $label }}
                </span>
            </div>
            <div class="ticket-message-meta">
                <span class="small text-muted">
                    {{ optional($timestamp)->format('d M Y, H:i') }}
                </span>
            </div>
        </div>

        <div class="ticket-message-body">
            {!! nl2br(e($body ?? '')) !!}
        </div>
    </div>
</div>
