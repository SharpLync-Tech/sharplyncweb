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

    const statusEl   = document.getElementById('cp-email-status');
    const errorEl    = document.getElementById('cp-email-error');
    const otpInputs  = Array.from(document.querySelectorAll('.cp-otp-input'));

    // Authenticator UI
    const authQrWrapper   = document.getElementById('cp-auth-qr-wrapper');
    const authSecretBlock = document.getElementById('cp-auth-secret-block');
    const authVerifyBlock = document.getElementById('cp-auth-verify-block');
    const authStatusEl    = document.getElementById('cp-auth-status');
    const authErrorEl     = document.getElementById('cp-auth-error');
    const authCodeInput   = document.getElementById('cp-auth-code');

    /* ============================================================
       FIX 1 — REMOVE restoreDBState()
       The toggles MUST NOT be forced back to DB values.
       ============================================================ */

    function clearOtp() {
        otpInputs.forEach(i => i.value = '');
        if (otpInputs[0]) otpInputs[0].focus();
    }

    function showMain() {
        screenMain.style.display  = 'block';
        screenEmail.style.display = 'none';
        screenAuth.style.display  = 'none';

        backEmailBtn.style.display = 'none';
        backAuthBtn.style.display  = 'none';

        errorEl.style.display   = 'none';
        statusEl.style.display  = 'none';

        sendBlock.style.display   = 'block';
        verifyBlock.style.display = 'none';

        clearOtp();

        // Reset Authenticator UI
        authQrWrapper.style.display   = 'none';
        authSecretBlock.style.display = 'none';
        authVerifyBlock.style.display = 'none';
        authStatusEl.style.display    = 'none';
        authErrorEl.style.display     = 'none';
        if (authCodeInput) authCodeInput.value = '';
    }

    function showEmailSetup() {
        screenMain.style.display  = 'none';
        screenEmail.style.display = 'block';
        screenAuth.style.display  = 'none';

        backEmailBtn.style.display = 'inline-block';
        backAuthBtn.style.display  = 'none';

        errorEl.style.display  = 'none';
        statusEl.style.display = 'none';

        clearOtp();
    }

    function showAuthSetup() {
        screenMain.style.display  = 'none';
        screenEmail.style.display = 'none';
        screenAuth.style.display  = 'block';

        backEmailBtn.style.display = 'none';
        backAuthBtn.style.display  = 'inline-block';

        authStatusEl.style.display = 'none';
        authErrorEl.style.display  = 'none';
        if (authCodeInput) authCodeInput.value = '';
    }

    function openModal(){
        modal.classList.add('cp-modal-visible');
        modal.setAttribute('aria-hidden', 'false');
        if(root) root.classList.add('modal-open');

        /* FIX 2 — DO NOT restoreDBState() */
        // restoreDBState();

        showMain();
    }

    function closeModal(){
        modal.classList.remove('cp-modal-visible');
        modal.setAttribute('aria-hidden','true');
        if(root) root.classList.remove('modal-open');

        /* FIX 3 — DO NOT restoreDBState() */
        // restoreDBState();

        showMain();
    }

    // Modal events
    if (openBtn) openBtn.addEventListener('click', openModal);
    closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

    modal.addEventListener('click', e=>{
        if (!sheet.contains(e.target)) closeModal();
    });

    // ============================================================
    // TOGGLE HANDLING (NOW WORKS!)
    // ============================================================

    if (emailToggle) {
        emailToggle.addEventListener('change', function(){
            if (this.checked) {
                showEmailSetup();
            } else {
                showMain();
            }
        });
    }

    if (authToggle) {
        authToggle.addEventListener('change', function(){
            if (this.checked) {
                if (emailToggle) emailToggle.checked = false;

                showAuthSetup();

                // Tell security.js to start QR setup
                document.dispatchEvent(new Event('cp-auth-start'));

            } else {
                showMain();
            }
        });
    }

    backEmailBtn.addEventListener('click', showMain);
    backAuthBtn.addEventListener('click', showMain);

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

})(); // END IIFE

}); // END DOMContentLoaded
