// public/js/services/services.js

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.services-cards');
    if (!container) return;

    container.addEventListener('click', (event) => {
        const toggle = event.target.closest('.tile-toggle');
        if (!toggle) return;

        const tile = toggle.closest('.service-tile');
        if (!tile) return;

        const isActive = tile.classList.contains('active');

        // Reset all tiles first
        const tiles = container.querySelectorAll('.service-tile');
        tiles.forEach(t => {
            t.classList.remove('active');
            const btn = t.querySelector('.tile-toggle');
            if (btn) btn.textContent = 'Learn More';
        });
        container.classList.remove('focus-one');

        // If the clicked one was NOT active, open it
        if (!isActive) {
            tile.classList.add('active');
            container.classList.add('focus-one');
            toggle.textContent = 'Close';
        }
        // If it *was* active, we just closed it by resetting above
    });
});
