window.ThreatCheckDragDrop = function () {

    const overlay   = document.getElementById('drop-overlay');
    const textarea  = document.getElementById('scam-text');
    const fileInput = document.querySelector('input[type="file"]');

    if (!overlay || !textarea || !fileInput) return;

    // ----------------------------------
    // Helper: show file label in textarea
    // ----------------------------------
    function showFileInTextarea(file) {
        textarea.value = `[Attached file: ${file.name}]`;
        textarea.focus();
    }

    // ----------------------------------
    // Browse file selection
    // ----------------------------------
    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            showFileInTextarea(this.files[0]);
        }
    });

    // ----------------------------------
    // Show overlay when dragging
    // ----------------------------------
    document.addEventListener('dragenter', (e) => {
        e.preventDefault();
        overlay.classList.add('active');
    });

    document.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    document.addEventListener('dragleave', (e) => {
        if (e.target === document.body) {
            overlay.classList.remove('active');
        }
    });

    // ----------------------------------
    // Drop handler
    // ----------------------------------
    document.addEventListener('drop', (e) => {
        e.preventDefault();
        overlay.classList.remove('active');

        const dt = e.dataTransfer;

        // ==============================
        // 1️⃣ FILE DROP (wins)
        // ==============================
        if (dt.files && dt.files.length > 0) {
            fileInput.files = dt.files;
            showFileInTextarea(dt.files[0]);

            console.log('[ThreatCheck] File dropped:', dt.files[0].name);
            return;
        }

        // ==============================
        // 2️⃣ TEXT DROP (Outlook / Gmail)
        // ==============================
        const text =
            dt.getData('text/plain') ||
            dt.getData('text/html');

        if (text && text.trim().length > 0) {
            textarea.value = stripHtml(text);
            fileInput.value = null;
            textarea.focus();

            console.log('[ThreatCheck] Text dropped');
        }
    });
};

// ----------------------------------
// Helper: strip HTML safely
// ----------------------------------
function stripHtml(input) {
    const div = document.createElement('div');
    div.innerHTML = input;
    return div.textContent || div.innerText || '';
}