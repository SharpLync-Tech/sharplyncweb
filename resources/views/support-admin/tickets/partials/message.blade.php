<div class="{{ $isCustomer ? 'admin-message-customer' : 'admin-message-staff' }}">
    <div class="admin-message-header">
        <span class="admin-message-author">{{ $authorName }}</span>
        <span class="admin-message-time">
            {{ optional($timestamp)->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
        </span>
    </div>

    <div class="{{ $isCustomer ? 'admin-message-customer' : 'admin-message-staff' }}">
    <div class="admin-message-header">
        <span class="admin-message-author">{{ $authorName }}</span>
        <span class="admin-message-time">
            {{ optional($timestamp)->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
        </span>
    </div>

    <div class="admin-message-body">
        {!! $body !!}

        {{-- Attachment (if exists) --}}
        @if(!empty($attachment_path ?? null))
            <div class="admin-message-attachment" style="margin-top:10px;">
                📎 <a href="{{ route('support-admin.attachment.download', $message_id) }}"
                      style="color:#0A4A7A; font-weight:600;"
                      target="_blank">
                    {{ $attachment_original_name }}
                </a>
                <div style="font-size:12px; opacity:.7;">
                    ({{ $attachment_mime }})
                </div>
            </div>
        @endif
    </div>
</div>


</div>
