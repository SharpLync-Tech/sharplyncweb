/*
 * File: public/js/admin-tickets.js
 * Version: v1.0 (Phase 1)
 * Description:
 * - Small enhancements for admin ticket UI
 * - Quick resolve confirmation
 */

document.addEventListener('DOMContentLoaded', function () {
    // Quick resolve buttons on index page
    const quickResolveButtons = document.querySelectorAll('.ticket-quick-resolve-btn');

    quickResolveButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ticketId = this.getAttribute('data-ticket-id');
            const url = this.getAttribute('data-ticket-resolve-url');

            if (!url) return;

            const confirmMsg = `Mark ticket #${ticketId} as resolved?`;
            if (!window.confirm(confirmMsg)) {
                return;
            }

            // Create a tiny form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const inputCsrf = document.createElement('input');
                inputCsrf.type = 'hidden';
                inputCsrf.name = '_token';
                inputCsrf.value = csrfToken.getAttribute('content');
                form.appendChild(inputCsrf);
            }

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        });
    });
});
