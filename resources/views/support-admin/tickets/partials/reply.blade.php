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

                {{-- Toolbar --}}
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
                        <label style="cursor:pointer;">
                            📤
                            <input type="file" name="attachment"
                                   id="admin-attachment"
                                   hidden>
                        </label>
                    </span>
                </div>

                {{-- Editor --}}
                <div id="admin-quill-editor" class="quill-editor"></div>

                {{-- Hidden HTML --}}
                <input type="hidden" name="message" id="admin-quill-html">
            </div>

            {{-- ============================================================
                 FILE PREVIEW (new)
            ============================================================ --}}
            <div id="admin-attachment-preview" style="display:none; margin-top:15px;">
                <div style="
                    padding:12px 14px;
                    background:#f3f8fb;
                    border:1px solid #dbe7ef;
                    border-radius:10px;
                    display:flex;
                    align-items:center;
                    gap:12px;
                ">
                    <div id="admin-attachment-thumb"></div>

                    <div style="flex:1;">
                        <div id="admin-attachment-name"
                            style="font-weight:600; color:#0A2A4D;"></div>
                        <div id="admin-attachment-size"
                            style="font-size:12px; opacity:.7;"></div>
                    </div>

                    <button type="button"
                            id="admin-attachment-remove"
                            style="
                                background:none;
                                border:none;
                                font-size:18px;
                                cursor:pointer;
                                color:#c00;
                            ">
                        ✖
                    </button>
                </div>
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


{{-- ============================================================
     FILE PREVIEW JS (safe, local to this partial)
============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput   = document.getElementById('admin-attachment');
    const previewBox  = document.getElementById('admin-attachment-preview');
    const thumbEl     = document.getElementById('admin-attachment-thumb');
    const nameEl      = document.getElementById('admin-attachment-name');
    const sizeEl      = document.getElementById('admin-attachment-size');
    const removeBtn   = document.getElementById('admin-attachment-remove');

    if (!fileInput) return;

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) {
            previewBox.style.display = 'none';
            return;
        }

        // Show preview box
        previewBox.style.display = 'block';

        // File info
        nameEl.textContent = file.name;
        sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

        // Thumbnail for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                thumbEl.innerHTML = `
                    <img src="${e.target.result}" style="
                        width:45px; height:45px;
                        object-fit:cover;
                        border-radius:6px;
                        border:1px solid #ccc;
                    ">
                `;
            };
            reader.readAsDataURL(file);
        } else {
            // Generic icon for non-images
            thumbEl.innerHTML = `
                <div style="
                    width:45px; height:45px;
                    background:#dfe8f0;
                    border-radius:6px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:22px;
                ">📄</div>
            `;
        }
    });

    // Remove attachment
    removeBtn.addEventListener('click', function () {
        fileInput.value = '';
        previewBox.style.display = 'none';
    });
});
</script>
