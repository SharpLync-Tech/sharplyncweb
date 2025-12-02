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

                        // Close the SSPIN section modal if needed
                    document.getElementById('cp-password-modal').classList.remove('cp-modal-visible');

                    // Show SSPIN success modal
                    const sspinModal = document.getElementById('cp-sspin-success-modal');
                    const root = document.querySelector('.cp-root');

                    sspinModal.classList.add('cp-modal-visible');
                    if (root) root.classList.add('modal-open');

                    // Auto-close after 2s
                    setTimeout(() => {
                        sspinModal.classList.remove('cp-modal-visible');
                        if (root) root.classList.remove('modal-open');
                    }, 2000);

                                        })
                                        .catch(() => alert("Error saving PIN."));
                                });
                            }

    })();


            /* ==========================================================
            LIVE PASSWORD VALIDATION + STRENGTH METER
            ========================================================== */
            (function () {

                const pass1 = document.getElementById('cp-new-password');
                const pass2 = document.getElementById('cp-confirm-password');
                const saveBtn = document.getElementById('cp-password-save');

                const barFill = document.getElementById('cp-pass-strength-fill');
                const barText = document.getElementById('cp-pass-strength-text');

                if (!pass1 || !pass2 || !saveBtn) return;

                // Inline message for mismatch / short password
                const msg = document.createElement('div');
                msg.style.fontSize = "0.85rem";
                msg.style.marginTop = "6px";
                msg.style.color = "#e84c4c";
                msg.style.display = "none";
                pass2.parentNode.insertBefore(msg, pass2.nextSibling);

                function passwordStrength(pw) {
                    let score = 0;

                    if (pw.length >= 8) score++;
                    if (pw.length >= 12) score++;
                    if (/[A-Z]/.test(pw)) score++;
                    if (/[a-z]/.test(pw)) score++;
                    if (/[0-9]/.test(pw)) score++;
                    if (/[^A-Za-z0-9]/.test(pw)) score++; // symbol

                    return score;
                }

                function renderStrength(score) {
                    const percent = Math.min((score / 6) * 100, 100);

                    barFill.style.width = percent + "%";

                    if (score <= 1) {
                        barFill.style.background = "#e84c4c";
                        barText.textContent = "Very Weak";
                    } else if (score <= 2) {
                        barFill.style.background = "#ff9800";
                        barText.textContent = "Weak";
                    } else if (score <= 3) {
                        barFill.style.background = "#ffc107";
                        barText.textContent = "Medium";
                    } else if (score <= 4) {
                        barFill.style.background = "#8bc34a";
                        barText.textContent = "Strong";
                    } else {
                        barFill.style.background = "#2CBFAE"; // SharpLync teal
                        barText.textContent = "Very Strong";
                    }
                }

                function validate() {
                    const p1 = pass1.value.trim();
                    const p2 = pass2.value.trim();

                    // Strength indicator updates no matter what
                    renderStrength(passwordStrength(p1));

                    // No password → disable everything
                    if (!p1) {
                        msg.style.display = "none";
                        saveBtn.disabled = true;
                        saveBtn.classList.add("disabled");
                        return;
                    }

                    // Too short
                    if (p1.length < 8) {
                        msg.textContent = "Password must be at least 8 characters.";
                        msg.style.display = "block";
                        saveBtn.disabled = true;
                        saveBtn.classList.add("disabled");
                        return;
                    }

                    // Mismatch
                    if (p1 !== p2) {
                        msg.textContent = "Passwords do not match.";
                        msg.style.display = "block";
                        saveBtn.disabled = true;
                        saveBtn.classList.add("disabled");
                        return;
                    }

                    // All good → enable Save
                    msg.style.display = "none";
                    saveBtn.disabled = false;
                    saveBtn.classList.remove("disabled");
                }

                pass1.addEventListener("input", validate);
                pass2.addEventListener("input", validate);

                // Start disabled
                saveBtn.disabled = true;
                saveBtn.classList.add("disabled");
            })();


            /* ==========================================================
            PASSWORD SUCCESS MODAL — CLOSE BUTTON
            ========================================================== */
            (function () {

                const successModal = document.getElementById('cp-password-success-modal');
                const successClose = document.getElementById('cp-password-success-close');
                const root = document.querySelector('.cp-root');

                if (successClose) {
                    successClose.addEventListener('click', () => {
                        successModal.classList.remove('cp-modal-visible');
                        if (root) root.classList.remove('modal-open');
                    });
                }

            })();

            /* ==========================================================
            SSPIN SUCCESS MODAL — CLOSE BUTTON + Auto-close
            ========================================================== */
            (function () {

                const sspinModal = document.getElementById('cp-sspin-success-modal');
                const sspinClose = document.getElementById('cp-sspin-success-close');
                const root = document.querySelector('.cp-root');

                if (sspinClose) {
                    sspinClose.addEventListener('click', () => {
                        sspinModal.classList.remove('cp-modal-visible');
                        if (root) root.classList.remove('modal-open');
                    });
                }

            })();



}); // END DOMContentLoaded
