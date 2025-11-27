/*
 * File: public/js/admin/admin-tickets.js
 * Description: Small enhancements for SharpLync admin ticket portal
 */

document.addEventListener('DOMContentLoaded', function () {
    // Quick resolve buttons
    document.querySelectorAll('.js-ticket-quick-resolve').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ticketId = this.getAttribute('data-ticket-id');
            const url = this.getAttribute('data-resolve-url');
            if (!url) return;

            const confirmMsg = `Mark ticket #${ticketId} as resolved?`;
            if (!window.confirm(confirmMsg)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]');
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

    // Auto-scroll conversation to bottom
    const convo = document.querySelector('.ticket-conversation-wrapper');
    if (convo) {
        convo.scrollTop = convo.scrollHeight;
    }
});
