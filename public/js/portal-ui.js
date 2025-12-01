document.addEventListener("DOMContentLoaded", function () {

    // ==========================================================
    // ORIGINAL 2FA MODAL CONTROLLER — UNCHANGED
    // ==========================================================
    (function () {
        const modal = document.getElementById('cp-security-modal');
        const openBtn = document.getElementById('cp-open-security-modal');
        const sheet = modal?.querySelector('.cp-modal-sheet');
        const closeBtns = modal?.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');
        const root = document.querySelector('.cp-root');

        function openModal() {
            modal.classList.add('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'false');
            if (root) root.classList.add('modal-open');
        }

        function closeModal() {
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'true');
            if (root) root.classList.remove('modal-open');
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtns) closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

        modal?.addEventListener('click', e => {
            if (!sheet.contains(e.target)) closeModal();
        });
    })();


    // ==========================================================
    // PASSWORD + SSPIN MODAL CONTROLLER
    // ==========================================================
    (function () {
        const passModal = document.getElementById('cp-password-modal');
        const openPassBtn = document.getElementById('cp-open-password-modal');
        const passSheet = passModal?.querySelector('.cp-modal-sheet');
        const passCloseBtns = passModal?.querySelectorAll('.cp-password-close');
        const root = document.querySelector('.cp-root');

        function openPassModal() {
            passModal.classList.add('cp-modal-visible');
            passModal.setAttribute('aria-hidden', 'false');
            if (root) root.classList.add('modal-open');
        }

        function closePassModal() {
            passModal.classList.remove('cp-modal-visible');
            passModal.setAttribute('aria-hidden', 'true');
            if (root) root.classList.remove('modal-open');
        }

        if (openPassBtn) openPassBtn.addEventListener('click', openPassModal);
        if (passCloseBtns) passCloseBtns.forEach(btn => btn.addEventListener('click', closePassModal));

        passModal?.addEventListener('click', e => {
            if (!passSheet.contains(e.target)) closePassModal();
        });
    })();


    // ==========================================================
    // DASHBOARD "Create/Manage" → OPEN MODAL
    // ==========================================================
    (function () {
        const btns = document.querySelectorAll('#cp-open-password-modal-from-preview');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                openPassBtn?.click();
            });
        });
    })();


    // ==========================================================
    // SSPIN CONTROLLER (UNMASKED)
    // ==========================================================
    (function () {

        const displayEl = document.getElementById('cp-sspin-display');
        const inputEl = document.getElementById('cp-sspin-input');
        const generateBtn = document.getElementById('cp-sspin-generate');
        const saveBtn = document.getElementById('cp-sspin-save');
        const previewEl = document.getElementById('cp-sspin-preview');

        if (!displayEl || !inputEl) return;

        // ----------------------------------------------------------
        // GENERATE NEW SSPIN
        // ----------------------------------------------------------
        if (generateBtn) {
            generateBtn.addEventListener('click', () => {

                fetch('/portal/security/sspin/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.cpCsrf
                    }
                })
                    .then(res => res.json())
                    .then(data => {

                        if (!data.success || !data.sspin) {
                            alert("Error generating PIN.");
                            return;
                        }

                        inputEl.value = data.sspin;
                        displayEl.textContent = data.sspin;

                        if (previewEl) previewEl.textContent = data.sspin;

                    })
                    .catch(() => alert("Error generating PIN."));
            });
        }


        // ----------------------------------------------------------
        // SAVE SSPIN
        // ----------------------------------------------------------
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {

                const pin = inputEl.value.trim();

                if (!/^[0-9]{6}$/.test(pin)) {
                    alert("PIN must be exactly 6 digits.");
                    return;
                }

                fetch('/portal/security/sspin/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.cpCsrf
                    },
                    body: JSON.stringify({ sspin: pin })
                })
                    .then(res => res.json())
                    .then(data => {

                        if (!data.success) {
                            alert("Error saving PIN.");
                            return;
                        }

                        displayEl.textContent = pin;
                        if (previewEl) previewEl.textContent = pin;

                        alert("Support PIN saved.");
                    })
                    .catch(() => alert("Error saving PIN."));
            });
        }

    })();

});
