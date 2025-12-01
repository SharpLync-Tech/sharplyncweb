// public/js/portal-ui.js
document.addEventListener("DOMContentLoaded", function () {

(function(){

    const modal     = document.getElementById('cp-security-modal');
    const openBtn   = document.getElementById('cp-open-security-modal');
    const sheet     = modal.querySelector('.cp-modal-sheet');
    const closeBtns = modal.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');
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
        screenMain.style.display  = 'block';
        screenEmail.style.display = 'none';
        screenAuth.style.display  = 'none';

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
        screenMain.style.display  = 'none';
        screenEmail.style.display = 'block';
        screenAuth.style.display  = 'none';

        if (backEmailBtn) backEmailBtn.style.display = 'inline-block';
        if (backAuthBtn)  backAuthBtn.style.display  = 'none';

        if (errorEl)  errorEl.style.display  = 'none';
        if (statusEl) statusEl.style.display = 'none';

        clearOtp();
    }

    function showAuthSetup() {
        screenMain.style.display  = 'none';
        screenEmail.style.display = 'none';
        screenAuth.style.display  = 'block';

        if (backEmailBtn) backEmailBtn.style.display = 'none';
        if (backAuthBtn)  backAuthBtn.style.display  = 'inline-block';

        if (authStatusEl) authStatusEl.style.display = 'none';
        if (authErrorEl)  authErrorEl.style.display  = 'none';
        if (authCodeInput) authCodeInput.value = '';
    }


    function openModal(){
        modal.classList.add('cp-modal-visible');
        modal.setAttribute('aria-hidden', 'false');
        if(root) root.classList.add('modal-open');
        showMain();
    }

    function closeModal(){
        modal.classList.remove('cp-modal-visible');
        modal.setAttribute('aria-hidden','true');
        if(root) root.classList.remove('modal-open');
        showMain();
    }

    // Modal events
    if (openBtn) openBtn.addEventListener('click', openModal);
    closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

    modal.addEventListener('click', e=>{
        if (!sheet.contains(e.target)) closeModal();
    });

    // Toggle logic
    if (emailToggle) {
        emailToggle.addEventListener('change', function(){
            this.checked ? showEmailSetup() : showMain();
        });
    }

    if (authToggle) {
        authToggle.addEventListener('change', function(){
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

    // OTP UX
    otpInputs.forEach((input, idx)=>{
        input.addEventListener('input', e=>{
            e.target.value = e.target.value.replace(/\D/g,'');
            if (e.target.value && idx < otpInputs.length-1) {
                otpInputs[idx+1].focus();
            }
        });

        input.addEventListener('keydown', e=>{
            if(e.key==='Backspace' && !e.target.value && idx>0){
                otpInputs[idx-1].focus();
            }
        });

        input.addEventListener('paste', e=>{
            e.preventDefault();
            const digits = (e.clipboardData.getData('text') || '')
                .replace(/\D/g,'')
                .slice(0,6)
                .split('');

            otpInputs.forEach((inp,i)=>inp.value = digits[i] || '');
            otpInputs[Math.min(digits.length-1,5)].focus();
        });
    });

})(); 

}); // END DOMContentLoaded

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
    // SSPIN — FULL FRONT-END CONTROLLER (UNCHANGED)
    // ==========================================================
    (function () {

        const displayEl = document.getElementById('cp-sspin-display');
        const inputEl = document.getElementById('cp-sspin-input');
        const generateBtn = document.getElementById('cp-sspin-generate');
        const saveBtn = document.getElementById('cp-sspin-save');
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



    // =======================================================================
// SharpLync Portal UI — Password Reset Button Handler (Updated Dec 2025)
// =======================================================================

(function () {

    const resetBtn = document.getElementById('cp-password-reset-request');

    if (!resetBtn) return;

    resetBtn.addEventListener('click', () => {

        // ✅ Uses NEW dynamic route defined in portal.blade.php
        fetch(window.cpRoutes.passwordSendReset, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.cpCsrf
            }
        })
            .then(res => res.json())
            .then(data => {

                if (!data.success) {
                    alert(data.message || "Could not send password reset link.");
                    return;
                }

                alert("Password reset email sent! Check your inbox.");
            })
            .catch(() => {
                alert("Server error sending password reset email.");
            });
    });

})();



}); // END DOMContentLoaded