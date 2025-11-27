<div class="{{ $isCustomer ? 'admin-message-customer' : 'admin-message-staff' }}">
    <div class="admin-message-header">
        <span class="admin-message-author">{{ $authorName }}</span>
        <span class="admin-message-time">
            {{ optional($timestamp)->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
        </span>
    </div>

    <div class="admin-message-body">
        {!! nl2br(e($body)) !!}
    </div>
</div>
