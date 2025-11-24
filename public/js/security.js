// public/js/security.js
(function(){

    const emailToggle = document.getElementById('cp-toggle-email');
    const authToggle  = document.getElementById('cp-toggle-auth');

    const sendBtn    = document.getElementById('cp-email-setup-send');
    const resendBtn  = document.getElementById('cp-email-setup-resend');
    const verifyBtn  = document.getElementById('cp-email-setup-verify');

    const sendBlock   = document.getElementById('cp-email-send-block');
    const verifyBlock = document.getElementById('cp-email-verify-block');

    const statusEl   = document.getElementById('cp-email-status');
    const errorEl    = document.getElementById('cp-email-error');
    const otpInputs  = Array.from(document.querySelectorAll('.cp-otp-input'));

    const routes = {
        emailSend:   window.cpRoutes?.emailSend,
        emailVerify: window.cpRoutes?.emailVerify,
        authStart:   window.cpRoutes?.authStart,
        authVerify:  window.cpRoutes?.authVerify,
        authDisable: window.cpRoutes?.authDisable
    };

    const csrf = window.cpCsrf;

    function getOtp(){
        return otpInputs.map(i => i.value).join('');
    }

    // ============================
    // EMAIL — SEND CODE
    // ============================
    async function sendCode(){
        statusEl.style.display = 'block';
        statusEl.textContent = "Sending verification code...";
        errorEl.style.display='none';

        try{
            const r = await fetch(routes.emailSend,{
                method:"POST",
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':csrf
                },
                body:JSON.stringify({})
            });

            const d = await r.json();
            if(!d.success) throw new Error(d.message);

            statusEl.textContent = "We've emailed you a 6-digit code.";
            sendBlock.style.display='none';
            verifyBlock.style.display='block';

        }catch(err){
            statusEl.style.display='none';
            errorEl.textContent = err.message || "Something went wrong.";
            errorEl.style.display='block';
        }
    }

    // ============================
    // EMAIL — VERIFY CODE
    // ============================
    async function verifyCode(){
        const code = getOtp();
        if(code.length !== 6){
            errorEl.textContent="Please enter the full 6-digit code.";
            errorEl.style.display='block';
            return;
        }

        try{
            const r = await fetch(routes.emailVerify,{
                method:"POST",
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':csrf
                },
                body:JSON.stringify({code})
            });

            const d = await r.json();
            if(!d.success) throw new Error(d.message);

            emailToggle.dataset.persistOn = "1";
            emailToggle.checked = true;

            statusEl.textContent = "Email Authentication is now enabled.";
            statusEl.style.display='block';
            errorEl.style.display='none';

        }catch(err){
            errorEl.textContent = err.message || "Invalid or expired code.";
            errorEl.style.display='block';
        }
    }

    if (sendBtn)   sendBtn.addEventListener('click', sendCode);
    if (resendBtn) resendBtn.addEventListener('click', sendCode);
    if (verifyBtn) verifyBtn.addEventListener('click', verifyCode);

    // ============================
    // AUTH APP — START / VERIFY / DISABLE
    // ============================
    const authStartBtn   = document.getElementById('cp-auth-start');
    const authVerifyBtn  = document.getElementById('cp-auth-verify');
    const authDisableBtn = document.getElementById('cp-auth-disable');

    const authQrWrapper   = document.getElementById('cp-auth-qr-wrapper');
    const authQrImg       = document.getElementById('cp-auth-qr');
    const authSecretBlock = document.getElementById('cp-auth-secret-block');
    const authSecretEl    = document.getElementById('cp-auth-secret');
    const authStatusEl    = document.getElementById('cp-auth-status');
    const authErrorEl     = document.getElementById('cp-auth-error');
    const authVerifyBlock = document.getElementById('cp-auth-verify-block');
    const authCodeInput   = document.getElementById('cp-auth-code');

    async function startAuthSetup() {
        authStatusEl.style.display = 'block';
        authStatusEl.textContent  = "Generating QR code...";
        authErrorEl.style.display = 'none';

        try {
            const r = await fetch(routes.authStart, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({})
            });

            const d = await r.json();
            if (!d.success) throw new Error(d.message);

            const secret  = d.secret;
            const qrImage = d.qr_image || null;

            if (qrImage) {
                authQrImg.src = qrImage;
                authQrWrapper.style.display = 'block';
            } else {
                authQrWrapper.style.display = 'none';
            }

            if (secret) {
                authSecretBlock.style.display = 'block';
                authSecretEl.textContent      = secret;
            }

            authVerifyBlock.style.display = 'block';
            authStatusEl.textContent      = "Scan the QR or enter the code in your app, then enter the 6-digit code below.";

        } catch (err) {
            authStatusEl.style.display = 'none';
            authErrorEl.textContent    = err.message || "Something went wrong starting setup.";
            authErrorEl.style.display  = 'block';
            if (authToggle) authToggle.checked = false;
        }
    }

    async function verifyAuthCode() {
        const code = (authCodeInput.value || '').replace(/\D/g, '');

        if (code.length !== 6) {
            authErrorEl.textContent = "Please enter the 6-digit code from your app.";
            authErrorEl.style.display = 'block';
            return;
        }

        authStatusEl.style.display = 'block';
        authStatusEl.textContent   = "Verifying code...";
        authErrorEl.style.display  = 'none';

        try {
            const r = await fetch(routes.authVerify, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({ code })
            });

            const d = await r.json();
            if (!d.success) throw new Error(d.message);

            authToggle.dataset.persistOn = "1";
            authToggle.checked           = true;

            if (emailToggle) {
                emailToggle.dataset.persistOn = "";
                emailToggle.checked           = false;
            }

            authStatusEl.textContent = "Authenticator App is now enabled.";

        } catch (err) {
            authStatusEl.style.display = 'none';
            authErrorEl.textContent    = err.message || "Invalid or expired code.";
            authErrorEl.style.display  = 'block';
        }
    }

    async function disableAuth() {
        authStatusEl.style.display = 'block';
        authStatusEl.textContent   = "Disabling Authenticator App...";
        authErrorEl.style.display  = 'none';

        try {
            const r = await fetch(routes.authDisable, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({})
            });

            const d = await r.json();
            if (!d.success) throw new Error(d.message);

            authToggle.dataset.persistOn = "";
            authToggle.checked           = false;

            authQrWrapper.style.display   = 'none';
            authSecretBlock.style.display = 'none';
            authVerifyBlock.style.display = 'none';

            authStatusEl.textContent = "Authenticator App has been disabled.";

        } catch (err) {
            authStatusEl.style.display = 'none';
            authErrorEl.textContent    = err.message || "Something went wrong disabling.";
            authErrorEl.style.display  = 'block';
        }
    }

    // Button hooks (still safe to keep)
    if (authStartBtn)   authStartBtn.addEventListener('click', startAuthSetup);
    if (authVerifyBtn)  authVerifyBtn.addEventListener('click', verifyAuthCode);
    if (authDisableBtn) authDisableBtn.addEventListener('click', disableAuth);

    // 🔵 NEW: listen for UI event from portal-ui.js
    document.addEventListener('cp-auth-start', startAuthSetup);

})();
