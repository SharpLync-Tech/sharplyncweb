document.addEventListener("DOMContentLoaded", function () {

    //
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


    //
    // ==========================================================
    // NEW PASSWORD & SSPIN MODAL CONTROLLER
    // ==========================================================
    (function () {

        const passModal = document.getElementById('cp-password-modal');
        const openPassBtn = document.getElementById('cp-open-password-modal'); // from Security card
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
    // DASHBOARD "Manage" BUTTON → OPEN PASSWORD/SSPIN MODAL
    // ==========================================================
    (function () {

        const manageBtn = document.getElementById('cp-open-password-modal-from-preview');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        if (manageBtn && openPassBtn) {
            manageBtn.addEventListener('click', () => {
                openPassBtn.click();
            });
        }

    })();



    //
    // ==========================================================
    // SSPIN — FULL FRONT-END CONTROLLER
    // ==========================================================
    (function () {

        const displayEl = document.getElementById('cp-sspin-display');
        const inputEl = document.getElementById('cp-sspin-input');
        const showBtn = document.getElementById('cp-sspin-toggle');
        const generateBtn = document.getElementById('cp-sspin-generate');
        const saveBtn = document.getElementById('cp-sspin-save');

        const dashboardPreview = document.getElementById('cp-sspin-preview');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        if (!displayEl || !inputEl) return; // modal not present

        let showing = false; // toggle state


        //
        // ----------------------------------------------------------
        // TOGGLE SHOW / HIDE PIN
        // ----------------------------------------------------------
        //
        if (showBtn) {
            showBtn.addEventListener('click', () => {

                if (!inputEl.value) return;

                showing = !showing;

                if (showing) {
                    displayEl.textContent = inputEl.value;
                    showBtn.textContent = "Hide PIN";
                } else {
                    displayEl.textContent = "••••••";
                    showBtn.textContent = "Show PIN";
                }
            });
        }


        //
        // ----------------------------------------------------------
        // GENERATE NEW PIN
        // ----------------------------------------------------------
        //
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
                            alert("Could not generate PIN.");
                            return;
                        }

                        // update modal
                        inputEl.value = data.sspin;
                        displayEl.textContent = showing ? data.sspin : "••••••";

                        // update dashboard preview
                        if (dashboardPreview) {
                            dashboardPreview.textContent = "••••••";
                        }

                    })
                    .catch(() => alert("Error generating PIN."));
            });
        }


        //
        // ----------------------------------------------------------
        // SAVE ENTERED PIN
        // ----------------------------------------------------------
        //
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
                            alert("Could not save PIN.");
                            return;
                        }

                        // Update modal
                        displayEl.textContent = showing ? pin : "••••••";

                        // Update dashboard preview
                        if (dashboardPreview) {
                            dashboardPreview.textContent = "••••••";
                        }

                        alert("Support PIN saved.");
                    })
                    .catch(() => alert("Error saving PIN."));
            });
        }

    })();

}); // END DOMContentLoaded
