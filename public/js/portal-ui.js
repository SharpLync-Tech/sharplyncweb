document.addEventListener("DOMContentLoaded", function () {

    //
    // ==========================================================
    // ORIGINAL SECURITY MODAL (2FA) — UNCHANGED
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



    //
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



    //
    // ==========================================================
    // DASHBOARD PREVIEW BUTTON → OPEN PASSWORD/SSPIN MODAL
    // ==========================================================
    (function () {

        const previewButton = document.getElementById('cp-open-password-modal-from-preview');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        if (previewButton && openPassBtn) {
            previewButton.addEventListener('click', () => openPassBtn.click());
        }

    })();



    //
    // ==========================================================
    // SSPIN CONTROLLER — COMPLETE, FINAL VERSION
    // ==========================================================
    (function () {

        // IDs MUST MATCH MODAL HTML EXACTLY
        const displayEl = document.getElementById('cp-sspin-display');
        const inputEl = document.getElementById('cp-sspin-input');
        const showBtn = document.getElementById('cp-sspin-toggle');
        const generateBtn = document.getElementById('cp-sspin-generate');
        const saveBtn = document.getElementById('cp-sspin-save');

        const dashboardPreview = document.getElementById('cp-sspin-preview');

        if (!displayEl || !inputEl) return;

        let showing = false;



        //
        // ----------------------------------------------------------
        // SHOW / HIDE PIN
        // ----------------------------------------------------------
        if (showBtn) {
            showBtn.addEventListener('click', () => {

                if (!inputEl.value) return;

                showing = !showing;

                displayEl.textContent = showing ? inputEl.value : "••••••";
                showBtn.textContent = showing ? "Hide PIN" : "Show PIN";
            });
        }



        //
        // ----------------------------------------------------------
        // GENERATE NEW PIN
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

                        if (!data.success) {
                            alert(data.message || "Could not generate PIN.");
                            return;
                        }

                        const newPin = data.sspin;

                        inputEl.value = newPin;
                        displayEl.textContent = showing ? newPin : "••••••";

                        if (dashboardPreview) {
                            dashboardPreview.textContent = "••••••";
                        }
                    })
                    .catch(() => alert("Error generating PIN."));
            });
        }



        //
        // ----------------------------------------------------------
        // SAVE PIN
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
                            alert(data.message || "Could not save PIN.");
                            return;
                        }

                        displayEl.textContent = showing ? pin : "••••••";

                        if (dashboardPreview) {
                            dashboardPreview.textContent = "••••••";
                        }

                        alert("Support PIN saved.");
                    })
                    .catch(() => alert("Error saving PIN."));
            });
        }

    })();

});
