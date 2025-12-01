document.addEventListener("DOMContentLoaded", function () {

    //
    // ==========================================================
    // ORIGINAL 2FA MODAL CONTROLLER — UNCHANGED
    // ==========================================================
    (function(){

        const modal     = document.getElementById('cp-security-modal');
        const openBtn   = document.getElementById('cp-open-security-modal');
        const sheet     = modal?.querySelector('.cp-modal-sheet');
        const closeBtns = modal?.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');
        const root      = document.querySelector('.cp-root');

        function openModal(){
            modal.classList.add('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'false');
            if(root) root.classList.add('modal-open');
        }

        function closeModal(){
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden','true');
            if(root) root.classList.remove('modal-open');
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtns) closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

        modal?.addEventListener('click', e => {
            if (!sheet.contains(e.target)) closeModal();
        });

    })();


    //
    // ==========================================================
    // PASSWORD & SSPIN MODAL CONTROLLER
    // ==========================================================
    (function(){

        const passModal     = document.getElementById('cp-password-modal');
        const openPassBtn   = document.getElementById('cp-open-password-modal');
        const passSheet     = passModal?.querySelector('.cp-modal-sheet');
        const passCloseBtns = passModal?.querySelectorAll('.cp-password-close');
        const root          = document.querySelector('.cp-root');

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
    // DASHBOARD "MANAGE" BUTTON → OPEN PASSWORD MODAL
    // ==========================================================
    (function(){

        const manageBtn  = document.getElementById('cp-open-password-modal-from-preview');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        if (manageBtn && openPassBtn) {
            manageBtn.addEventListener('click', () => openPassBtn.click());
        }

    })();


    //
    // ==========================================================
    // SSPIN FULL LOGIC (SHOW, GENERATE, SAVE)
    // ==========================================================
    (function() {

        const showBtn     = document.getElementById('cp-sspin-show');
        const genBtn      = document.getElementById('cp-sspin-generate');
        const input       = document.getElementById('cp-sspin-input');
        const display     = document.getElementById('cp-sspin-display');     // modal display
        const saveBtn     = document.querySelector('#cp-password-modal button.cp-btn.cp-teal-btn:last-of-type');
        const previewSpan = document.getElementById('cp-sspin-preview');      // dashboard masked preview

        let revealed = false;
        const MASKED = '••••••';


        // --- Show / Hide SSPIN ---
        if (showBtn && display) {
            showBtn.addEventListener('click', () => {
                if (!revealed) {
                    // Show actual PIN safely
                    display.textContent = input.value || display.dataset.realPin || MASKED;
                    showBtn.textContent = "Hide PIN";
                    revealed = true;
                } else {
                    // Hide back to bullets
                    display.textContent = MASKED;
                    showBtn.textContent = "Show PIN";
                    revealed = false;
                }
            });
        }


        // --- Generate New SSPIN ---
        if (genBtn && display && input) {
            genBtn.addEventListener('click', async () => {

                try {
                    const res = await fetch('/portal/security/sspin/generate', {
                        method: 'POST',
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": window.cpCsrf
                        }
                    });

                    const data = await res.json();

                    if (data.success) {
                        const newPin = data.pin.toString();

                        // Update modal display + input
                        input.value = newPin;
                        display.dataset.realPin = newPin;

                        if (revealed) display.textContent = newPin;
                        else display.textContent = MASKED;

                        // Update dashboard preview immediately
                        if (previewSpan) previewSpan.textContent = MASKED;
                    }

                } catch (e) {
                    console.error("SSPIN generation error:", e);
                }
            });
        }


        // --- Save SSPIN ---
        if (saveBtn && input) {
            saveBtn.addEventListener('click', async () => {

                const newPin = input.value.trim();

                if (!/^\d{6}$/.test(newPin)) {
                    alert("SSPIN must be 6 digits only.");
                    return;
                }

                try {
                    const res = await fetch('/portal/security/sspin/save', {
                        method: 'POST',
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": window.cpCsrf
                        },
                        body: JSON.stringify({ sspin: newPin })
                    });

                    const data = await res.json();

                    if (data.success) {

                        // Update modal display
                        display.dataset.realPin = newPin;
                        if (revealed) display.textContent = newPin;
                        else display.textContent = MASKED;

                        // Update dashboard preview
                        if (previewSpan) {
                            previewSpan.textContent = MASKED;
                        }

                        alert("Support PIN updated successfully.");
                    }

                } catch (e) {
                    console.error("SSPIN save error:", e);
                }
            });
        }

    })();

}); // END DOMContentLoaded
