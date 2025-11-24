{{-- New AUTHENTICATOR APP 2FA script --}}
<script>
(function(){

    const backdrop   = document.getElementById('login-app2fa-backdrop');
    if (!backdrop) return; // no app modal on this request

    const modalSheet = backdrop.querySelector('.cp-modal-sheet');
    const closeBtn   = document.getElementById('login-app2fa-close');
    const codeInput  = document.getElementById('login-app2fa-code');
    const submitBtn  = document.getElementById('login-app2fa-submit');
    const errorBox   = document.getElementById('login-app2fa-error');

    // Close logic
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e){
            e.preventDefault();
            backdrop.classList.remove('cp-modal-visible');
        });
    }

    function getCode() {
        if (!codeInput) return '';
        return (codeInput.value || '').replace(/\D/g, '').slice(0, 6);
    }

    function showError(msg) {
        if (!errorBox || !modalSheet) return;
        errorBox.innerText = msg || 'Something went wrong.';
        errorBox.style.display = 'block';

        modalSheet.classList.remove('shake');
        void modalSheet.offsetWidth;
        modalSheet.classList.add('shake');
    }

    if (codeInput) {
        codeInput.addEventListener('input', function(e){
            const cleaned = (e.target.value || '').replace(/\D/g, '').slice(0, 6);
            e.target.value = cleaned;
        });

        codeInput.addEventListener('keydown', function(e){
            if (e.key === 'Enter' && submitBtn) {
                e.preventDefault();
                submitBtn.click();
            }
        });

        setTimeout(() => codeInput.focus(), 100);
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function(){
            const code = getCode();

            if (code.length !== 6) {
                showError('Please enter the full 6-digit code.');
                return;
            }

            errorBox.style.display = 'none';

            fetch("{{ route('customer.login.2fa.verify-app') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    window.location = res.redirect;
                    return;
                }
                if (codeInput) {
                    codeInput.value = '';
                    codeInput.focus();
                }
                showError(res.message || 'Invalid or expired code.');
            })
            .catch(() => {
                showError('Something went wrong verifying your code.');
            });
        });
    }

})();
</script>
