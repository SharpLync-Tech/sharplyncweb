
/* public/js/support/support.js */
/* SharpLync Support Module V1
   Status / priority dropdown handling.
*/

document.addEventListener('DOMContentLoaded', function () {
    if (window.console && console.log) {
        console.log('SharpLync Support Module JS loaded (dropdowns active).');
    }

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

    function closeAllDropdowns(exceptEl) {
        document.querySelectorAll('.support-dropdown.is-open').forEach(function (panel) {
            if (panel !== exceptEl) {
                panel.classList.remove('is-open');
            }
        });
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.support-dropdown-toggle');
        const option = event.target.closest('.support-dropdown-option');

        // Toggle open / close
        if (toggle) {
            event.preventDefault();
            const wrapper = toggle.closest('.support-meta-control');
            const panel = wrapper ? wrapper.querySelector('.support-dropdown') : null;

            if (!panel) {
                return;
            }

            const isOpen = panel.classList.contains('is-open');
            closeAllDropdowns(isOpen ? panel : null);

            if (!isOpen) {
                panel.classList.add('is-open');
            } else {
                panel.classList.remove('is-open');
            }
            return;
        }

        // Option selected
        if (option) {
            event.preventDefault();
            const panel = option.closest('.support-dropdown');
            const wrapper = option.closest('.support-meta-control');
            const toggleBtn = wrapper ? wrapper.querySelector('.support-dropdown-toggle') : null;

            if (!panel || !toggleBtn) {
                return;
            }

            const newValue = option.dataset.value;
            const type = toggleBtn.dataset.type || 'status';
            const updateUrl = toggleBtn.dataset.updateUrl;

            panel.classList.remove('is-open');

            if (!updateUrl || !csrfToken) {
                console.warn('Support dropdown: missing URL or CSRF token.');
                return;
            }

            const payload = {};
            if (type === 'priority') {
                payload.priority = newValue;
            } else {
                payload.status = newValue;
            }

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    // We don't strictly need response JSON; ignore body on success.
                    return response.json().catch(function () { return {}; });
                })
                .then(function () {
                    var previous = toggleBtn.dataset.current;

                    if (type === 'priority') {
                        if (previous) {
                            toggleBtn.classList.remove('support-chip-' + previous);
                        }
                        toggleBtn.classList.add('support-chip-' + newValue);
                        toggleBtn.textContent =
                            newValue.charAt(0).toUpperCase() + newValue.slice(1) + ' priority';
                    } else {
                        if (previous) {
                            toggleBtn.classList.remove('support-badge-' + previous);
                        }
                        toggleBtn.classList.add('support-badge-' + newValue);
                        toggleBtn.textContent = newValue.replace(/_/g, ' ').toUpperCase();
                    }

                    toggleBtn.dataset.current = newValue;

                    // Mark active option
                    panel.querySelectorAll('.support-dropdown-option').forEach(function (btn) {
                        btn.classList.toggle('is-active', btn.dataset.value === newValue);
                    });
                })
                .catch(function (error) {
                    console.error('Support dropdown update failed:', error);
                    alert('Sorry, we could not update that right now. Please refresh and try again.');
                });

            return;
        }

        // Clicked somewhere else: close all dropdowns
        if (!event.target.closest('.support-dropdown')) {
            closeAllDropdowns(null);
        }
    });
});
