// public/js/portal-ui.js
document.addEventListener("DOMContentLoaded", function () {

    /* ==========================================================
       SECURITY MODAL (2FA SETTINGS) + VIEW SWITCHING
    ========================================================== */
    (function () {
        const modal     = document.getElementById('cp-security-modal');
        const openBtn   = document.getElementById('cp-open-security-modal');
        const sheet     = modal ? modal.querySelector('.cp-modal-sheet') : null;
        const closeBtns = modal ? modal.querySelectorAll('.cp-modal-close, .cp-modal-close-btn') : [];
        const root      = document.querySelector('.cp-root');

        const emailToggle = document.getElementById('cp-toggle-email');
        const authToggle  = document.getElementById('cp-toggle-auth');

        const screenMain  = document.getElementById('cp-modal-screen-main');
        const screenEmail = document.getElementById('cp-modal-screen-email-setup');
        const screenAuth  = document.getElementById('cp-modal-screen-auth-setup');

        const backEmailBtn = document.getElementById('cp-email-setup-back');
        const backAuthBtn  = document.getElementById('cp-auth-setup-back');

        const sendBlock   = document.getElementById('cp-email-send-block');
        const verifyBlock = document.getElementById('cp-email-verify-block');

        const statusEl = document.getElementById('cp-email-status');
        const errorEl  = document.getElementById('cp-email-error');

        const otpInputs  = Array.from(document.querySelectorAll('.cp-otp-input'));

        // Authenticator UI
        const authQrWrapper   = document.getElementById('cp-auth-qr-wrapper');
        const authSecretBlock = document.getElementById('cp-auth-secret-block');
        const authVerifyBlock = document.getElementById('cp-auth-verify-block');
        const authStatusEl    = document.getElementById('cp-auth-status');
        const authErrorEl     = document.getElementById('cp-auth-error');
        const authCodeInput   = document.getElementById('cp-auth-code');

        function clearOtp() {
            otpInputs.forEach(i => i.value = '');
            if (otpInputs[0]) otpInputs[0].focus();
        }

        function showMain() {
            if (screenMain)  screenMain.style.display  = 'block';
            if (screenEmail) screenEmail.style.display = 'none';
            if (screenAuth)  screenAuth.style.display  = 'none';

            if (backEmailBtn) backEmailBtn.style.display = 'none';
            if (backAuthBtn)  backAuthBtn.style.display  = 'none';

            if (errorEl)  errorEl.style.display  = 'none';
            if (statusEl) statusEl.style.display = 'none';

            if (sendBlock)   sendBlock.style.display   = 'block';
            if (verifyBlock) verifyBlock.style.display = 'none';

            clearOtp();

            if (authQrWrapper)   authQrWrapper.style.display   = 'none';
            if (authSecretBlock) authSecretBlock.style.display = 'none';
            if (authVerifyBlock) authVerifyBlock.style.display = 'none';
            if (authStatusEl)    authStatusEl.style.display    = 'none';
            if (authErrorEl)     authErrorEl.style.display     = 'none';
            if (authCodeInput)   authCodeInput.value = '';
        }

        function showEmailSetup() {
            if (screenMain)  screenMain.style.display  = 'none';
            if (screenEmail) screenEmail.style.display = 'block';
            if (screenAuth)  screenAuth.style.display  = 'none';

            if (backEmailBtn) backEmailBtn.style.display = 'inline-block';
            if (backAuthBtn)  backAuthBtn.style.display  = 'none';

            if (errorEl)  errorEl.style.display  = 'none';
            if (statusEl) statusEl.style.display = 'none';

            clearOtp();
        }

        function showAuthSetup() {
            if (screenMain)  screenMain.style.display  = 'none';
            if (screenEmail) screenEmail.style.display = 'none';
            if (screenAuth)  screenAuth.style.display  = 'block';

            if (backEmailBtn) backEmailBtn.style.display = 'none';
            if (backAuthBtn)  backAuthBtn.style.display  = 'inline-block';

            if (authStatusEl) authStatusEl.style.display = 'none';
            if (authErrorEl)  authErrorEl.style.display  = 'none';
            if (authCodeInput) authCodeInput.value = '';
        }

        function openModal() {
            if (!modal) return;
            modal.classList.add('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'false');
            if (root) root.classList.add('modal-open');
            showMain();
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'true');
            if (root) root.classList.remove('modal-open');
            showMain();
        }

        // Modal events
        if (openBtn) openBtn.addEventListener('click', openModal);
        closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

        if (modal && sheet) {
            modal.addEventListener('click', e => {
                if (!sheet.contains(e.target)) closeModal();
            });
        }

        // Toggle logic
        if (emailToggle) {
            emailToggle.addEventListener('change', function () {
                this.checked ? showEmailSetup() : showMain();
            });
        }

        if (authToggle) {
            authToggle.addEventListener('change', function () {
                if (this.checked) {
                    if (emailToggle) emailToggle.checked = false;
                    showAuthSetup();
                    document.dispatchEvent(new Event('cp-auth-start'));
                } else {
                    showMain();
                }
            });
        }

        if (backEmailBtn) backEmailBtn.addEventListener('click', showMain);
        if (backAuthBtn)  backAuthBtn.addEventListener('click', showMain);

        // OTP behaviour
        otpInputs.forEach((input, idx) => {
            input.addEventListener('input', e => {
                e.target.value = e.target.value.replace(/\D/g, '');
                if (e.target.value && idx < otpInputs.length - 1) {
                    otpInputs[idx + 1].focus();
                }
            });

            input.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                    otpInputs[idx - 1].focus();
                }
            });

            input.addEventListener('paste', e => {
                e.preventDefault();
                const digits = (e.clipboardData.getData('text') || '')
                    .replace(/\D/g, '')
                    .slice(0, 6)
                    .split('');

                otpInputs.forEach((inp, i) => inp.value = digits[i] || '');
                otpInputs[Math.min(digits.length - 1, 5)].focus();
            });
        });
    })();


    /* ==========================================================
       PASSWORD & SSPIN MODAL CONTROLLER
    ========================================================== */
    (function () {
        const passModal     = document.getElementById('cp-password-modal');
        const openPassBtn   = document.getElementById('cp-open-password-modal');
        const passSheet     = passModal ? passModal.querySelector('.cp-modal-sheet') : null;
        const passCloseBtns = passModal ? passModal.querySelectorAll('.cp-password-close') : [];
        const root          = document.querySelector('.cp-root');

        function openPassModal() {
            if (!passModal) return;
            passModal.classList.add('cp-modal-visible');
            passModal.setAttribute('aria-hidden', 'false');
            if (root) root.classList.add('modal-open');
        }

        function closePassModal() {
            if (!passModal) return;
            passModal.classList.remove('cp-modal-visible');
            passModal.setAttribute('aria-hidden', 'true');
            if (root) root.classList.remove('modal-open');
        }

        if (openPassBtn) openPassBtn.addEventListener('click', openPassModal);
        passCloseBtns.forEach(btn => btn.addEventListener('click', closePassModal));

        if (passModal && passSheet) {
            passModal.addEventListener('click', e => {
                if (!passSheet.contains(e.target)) closePassModal();
            });
        }

        // Dashboard "Manage" → open password/SSPIN modal
        const manageBtn = document.getElementById('cp-open-password-modal-from-preview');
        if (manageBtn && openPassBtn) {
            manageBtn.addEventListener('click', () => openPassBtn.click());
        }
    })();


    /* ==========================================================
       SSPIN CONTROLLER — UNCHANGED
    ========================================================== */
    (function () {

        const displayEl        = document.getElementById('cp-sspin-display');
        const inputEl          = document.getElementById('cp-sspin-input');
        const generateBtn      = document.getElementById('cp-sspin-generate');
        const saveBtn          = document.getElementById('cp-sspin-save');
        const dashboardPreview = document.getElementById('cp-sspin-preview');

        if (!displayEl || !inputEl) return;

        // Generate PIN
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

                        inputEl.value = data.sspin;
                        displayEl.textContent = data.sspin;

                        if (dashboardPreview) {
                            dashboardPreview.textContent = data.sspin;
                        }

                    })
                    .catch(() => alert("Error generating PIN."));
            });
        }

        // Save PIN
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

                        displayEl.textContent = pin;

                        if (dashboardPreview) {
                            dashboardPreview.textContent = pin;
                        }

                        alert("Support PIN saved.");
                    })
                    .catch(() => alert("Error saving PIN."));
            });
        }

    })();


    /* ==========================================================
       DIRECT PASSWORD UPDATE (NO OLD PASSWORD)
    ========================================================== */
    (function () {

        const savePasswordBtn  = document.getElementById('cp-password-save');
        const newPassInput     = document.getElementById('cp-new-password');
        const confirmPassInput = document.getElementById('cp-confirm-password');

        if (!savePasswordBtn || !newPassInput || !confirmPassInput) return;

        savePasswordBtn.addEventListener('click', () => {

            const pass     = newPassInput.value.trim();
            const confirm  = confirmPassInput.value.trim();

            if (pass.length < 8) {
                alert("Password must be at least 8 characters.");
                return;
            }

            if (pass !== confirm) {
                alert("Passwords do not match.");
                return;
            }

            fetch('/portal/security/password/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.cpCsrf
                },
                body: JSON.stringify({
                    password: pass
                })
            })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        alert(data.message || "Could not update password.");
                        return;
                    }

                    alert("Password updated successfully.");
                    newPassInput.value     = "";
                    confirmPassInput.value = "";
                })
                .catch(() => alert("Server error updating password."));
        });

    })();

}); // END DOMContentLoaded
