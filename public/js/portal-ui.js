// ======================================================================
// SharpLync Portal UI Controller
// Version 3.1  (Create SSPIN → Opens Password Modal)
// ======================================================================

document.addEventListener("DOMContentLoaded", function () {

    // ==========================================================
    // ORIGINAL 2FA MODAL CONTROLLER
    // ==========================================================
    (function () {

        const modal     = document.getElementById('cp-security-modal');
        const openBtn   = document.getElementById('cp-open-security-modal');
        const sheet     = modal?.querySelector('.cp-modal-sheet');
        const closeBtns = modal?.querySelectorAll('.cp-modal-close, .cp-modal-close-btn');
        const root      = document.querySelector('.cp-root');

        function openModal() {
            modal.classList.add('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'false');
            root?.classList.add('modal-open');
        }

        function closeModal() {
            modal.classList.remove('cp-modal-visible');
            modal.setAttribute('aria-hidden', 'true');
            root?.classList.remove('modal-open');
        }

        openBtn?.addEventListener('click', openModal);
        closeBtns?.forEach(btn => btn.addEventListener('click', closeModal));

        modal?.addEventListener('click', e => {
            if (!sheet.contains(e.target)) closeModal();
        });

    })();


    // ==========================================================
    // PASSWORD + SSPIN MODAL CONTROLLER
    // ==========================================================
    (function () {

        const passModal     = document.getElementById('cp-password-modal');
        const openPassBtn   = document.getElementById('cp-open-password-modal');   // Security card button
        const passSheet     = passModal?.querySelector('.cp-modal-sheet');
        const passCloseBtns = passModal?.querySelectorAll('.cp-password-close');
        const root          = document.querySelector('.cp-root');

        function openPassModal() {
            passModal.classList.add('cp-modal-visible');
            passModal.setAttribute('aria-hidden', 'false');
            root?.classList.add('modal-open');
        }

        function closePassModal() {
            passModal.classList.remove('cp-modal-visible');
            passModal.setAttribute('aria-hidden', 'true');
            root?.classList.remove('modal-open');
        }

        openPassBtn?.addEventListener('click', openPassModal);
        passCloseBtns?.forEach(btn => btn.addEventListener('click', closePassModal));

        passModal?.addEventListener('click', e => {
            if (!passSheet.contains(e.target)) closePassModal();
        });

    })();


    // ==========================================================
    // SSPIN PREVIEW — MANAGE → OPEN PASSWORD MODAL
    // ==========================================================
    (function () {

        const manageBtn = document.getElementById('cp-open-password-modal-from-preview');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        manageBtn?.addEventListener('click', () => {
            openPassBtn.click();
        });

    })();


    // ==========================================================
    // SSPIN PREVIEW — CREATE → OPEN PASSWORD MODAL
    // ==========================================================
    (function () {

        const createBtn = document.getElementById('cp-create-sspin-btn');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        createBtn?.addEventListener('click', () => {
            openPassBtn.click();
        });

    })();

});
