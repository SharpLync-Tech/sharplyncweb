@php
    $isCustomer   = $isCustomer ?? false;
    $authorName   = $authorName ?? 'User';
    $timestamp    = $timestamp ?? now();
    $label        = $label ?? ($isCustomer ? 'Customer' : 'Support');
@endphp

<div class="support-admin-message {{ $isCustomer ? 'is-customer' : 'is-staff' }}">
    <div class="support-admin-message-header">
        <div class="support-admin-message-author">
            {{ $authorName }}
            <span class="support-admin-message-pill">
                {{ $label }}
            </span>
        </div>
        <div class="support-admin-message-time">
            {{ optional($timestamp)->format('d M Y, H:i') }}
        </div>
    </div>
    <div class="support-admin-message-body">
        {!! nl2br(e($body ?? '')) !!}
    </div>
</div>
