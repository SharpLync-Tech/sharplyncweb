/* public/js/support-admin/support-admin.js */
/* SharpLync Support Admin Module
   - Status & priority dropdowns
   - Auto-scroll to latest messages
   - Collapsible "View earlier conversation(s)"
*/

document.addEventListener('DOMContentLoaded', function () {

    /* -----------------------------------------------------
       ADMIN DROPDOWN HANDLING (status / priority)
    ------------------------------------------------------*/

    // Toggle dropdown open/closed
    document.querySelectorAll('.support-admin-dropdown-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const type = this.getAttribute('data-dropdown-toggle');
            if (!type) return;

            const wrapper = this.closest('.support-admin-meta-control');
            const panel = wrapper
                ? wrapper.querySelector('[data-dropdown-panel="' + type + '"]')
                : null;

            if (!panel) return;

            const isOpen = panel.classList.contains('is-open');

            // Close any open dropdowns
            document.querySelectorAll('.support-admin-dropdown.is-open').forEach(function (openPanel) {
                openPanel.classList.remove('is-open');
            });

            // Toggle the clicked one
            if (!isOpen) {
                panel.classList.add('is-open');
            }
        });
    });

    // Click outside closes dropdowns
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.support-admin-meta-control')) {
            document.querySelectorAll('.support-admin-dropdown.is-open').forEach(function (panel) {
                panel.classList.remove('is-open');
            });
        }
    });


    /* -----------------------------------------------------
       COLLAPSIBLE "EARLIER CONVERSATION(S)" (Admin)
    ------------------------------------------------------*/

    const olderToggle = document.querySelector('[data-admin-older-toggle]');
    const olderContainer = document.querySelector('[data-admin-older-container]');

    if (olderToggle && olderContainer) {
        const closedLabel = 'View earlier conversation(s)';
        const openLabel = 'Hide earlier conversation(s)';

        olderToggle.addEventListener('click', function () {
            const isHidden = olderContainer.hasAttribute('hidden');

            if (isHidden) {
                olderContainer.removeAttribute('hidden');
                olderToggle.textContent = openLabel;
                olderToggle.classList.add('is-open');
            } else {
                olderContainer.setAttribute('hidden', 'hidden');
                olderToggle.textContent = closedLabel;
                olderToggle.classList.remove('is-open');
            }
        });
    }


    /* -----------------------------------------------------
       AUTO SCROLL TO LATEST (kept from your version)
    ------------------------------------------------------*/

    const thread = document.querySelector('.support-admin-thread-list');
    if (thread) {
        thread.scrollTop = thread.scrollHeight;
    }

});
