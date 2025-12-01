@if($ticket->status !== 'closed')
    <div class="support-admin-reply-card">
        <h2 class="support-admin-reply-title">Add a reply</h2>

        <form method="POST"
              action="{{ route('support-admin.tickets.reply', $ticket) }}"
              enctype="multipart/form-data"
              class="support-admin-form">
            @csrf

            <div class="support-admin-form-group">
                <label class="support-admin-label">Your message</label>

                {{-- Quill Toolbar --}}
                <div id="admin-quill-toolbar" class="quill-toolbar">
                    <span class="ql-formats">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                    </span>

                    <span class="ql-formats">
                        <button class="ql-list" value="bullet"></button>
                    </span>

                    <span class="ql-formats">
                        <button class="ql-emoji"></button>
                    </span>

                    <span class="ql-formats attach-btn">
                        <label>
                            📤
                            <input type="file" name="attachment" hidden>
                        </label>
                    </span>
                </div>

                {{-- Quill Editor --}}
                <div id="admin-quill-editor" class="quill-editor"></div>

                {{-- Hidden HTML field --}}
                <input type="hidden" name="message" id="admin-quill-html">
            </div>

            <div class="support-admin-form-actions">
                <button type="submit" class="support-admin-btn-primary">
                    Send reply
                </button>
            </div>
        </form>
    </div>

@else
    <div class="support-admin-closed-note">
        <strong>This ticket is closed.</strong>
        <span class="support-admin-closed-text">
            Change the status above if you need to reopen and reply again.
        </span>
    </div>
@endif
