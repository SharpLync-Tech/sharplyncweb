document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.support-admin-dropdown-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const type = this.getAttribute('data-dropdown-toggle');
            if (!type) return;

            const panel = this.closest('.support-admin-meta-control')
                .querySelector('[data-dropdown-panel="' + type + '"]');

            if (!panel) return;

            const isOpen = panel.classList.contains('is-open');

            document.querySelectorAll('.support-admin-dropdown.is-open').forEach(function (openPanel) {
                openPanel.classList.remove('is-open');
            });

            if (!isOpen) {
                panel.classList.add('is-open');
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.support-admin-meta-control')) {
            document.querySelectorAll('.support-admin-dropdown.is-open').forEach(function (openPanel) {
                openPanel.classList.remove('is-open');
            });
        }
    });

    const thread = document.querySelector('.support-admin-thread-list');
    if (thread) {
        thread.scrollTop = thread.scrollHeight;
    }
});
