window.ThreatCheckDragDrop = function () {

    const overlay    = document.getElementById('drop-overlay');
    const textarea   = document.querySelector('textarea[name="message"]');
    const fileInput  = document.getElementById('file-input');
    const fileBox    = document.getElementById('attached-file');
    const fileNameEl = fileBox?.querySelector('.file-name');
    const removeBtn  = fileBox?.querySelector('.remove-file');

    if (!overlay || !textarea || !fileInput || !fileBox) return;

    function showFile(name) {
        textarea.value = `[Attached file: ${name}]`;
        fileNameEl.textContent = name;
        fileBox.style.display = 'flex';
    }

    function clearFile() {
        fileInput.value = null;
        textarea.value = '';
        fileBox.style.display = 'none';
    }

    // Drag overlay
    document.addEventListener('dragenter', e => {
        e.preventDefault();
        overlay.classList.add('active');
    });

    document.addEventListener('dragover', e => e.preventDefault());

    document.addEventListener('dragleave', e => {
        if (e.target === document.body) overlay.classList.remove('active');
    });

    document.addEventListener('drop', e => {
        e.preventDefault();
        overlay.classList.remove('active');

        const dt = e.dataTransfer;

        // FILE DROP
        if (dt.files && dt.files.length > 0) {
            fileInput.files = dt.files;
            showFile(dt.files[0].name);
            return;
        }

        // TEXT DROP
        const text = dt.getData('text/plain') || dt.getData('text/html');
        if (text) {
            textarea.value = stripHtml(text);
            clearFile();
        }
    });

    // Browse selection
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            showFile(fileInput.files[0].name);
        }
    });

    // Remove button
    removeBtn.addEventListener('click', clearFile);
};

// Strip HTML helper
function stripHtml(input) {
    const div = document.createElement('div');
    div.innerHTML = input;
    return div.textContent || div.innerText || '';
}
