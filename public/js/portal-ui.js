/* ============================================================
   FILE: portal-ui.js
   PURPOSE: Control both modals + SSPIN preview link
   UPDATED SECTIONS ARE CLEARLY MARKED
   ============================================================ */

document.addEventListener("DOMContentLoaded", function () {

    // ============================================================
    // ORIGINAL 2FA MODAL CONTROLLER — UNCHANGED
    // ============================================================
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



    // ============================================================
    // 🔥 UPDATED SECTION #1 — PASSWORD & SSPIN MODAL
    // ============================================================
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



    // ============================================================
    // 🔥 UPDATED SECTION #2 — DASHBOARD SSPIN "MANAGE" BUTTON
    // ============================================================
    (function(){

        const manageBtn = document.getElementById('cp-open-password-modal-from-preview');
        const openPassBtn = document.getElementById('cp-open-password-modal');

        if (manageBtn && openPassBtn) {
            manageBtn.addEventListener('click', () => {
                openPassBtn.click();
            });
        }

    })();

}); // END DOMContentLoaded
