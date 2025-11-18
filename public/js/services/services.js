document.addEventListener('DOMContentLoaded', () => {
    const tiles = Array.from(document.querySelectorAll('.service-tile'));
    const container = document.querySelector('.services-cards');

    if (!tiles.length || !container) return;

    tiles.forEach(tile => {
        const toggleBtn = tile.querySelector('.tile-toggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
            const alreadyActive = tile.classList.contains('active');

            // Close all tiles
            tiles.forEach(t => t.classList.remove('active'));

            // Remove focus mode if closing the only active tile
            if (alreadyActive) {
                container.classList.remove('focus-one');
                return;
            }

            // Activate selected tile
            tile.classList.add('active');

            // Enable focus mode (fade out others)
            container.classList.add('focus-one');

            // Change button text to Close
            toggleBtn.textContent = 'Close';

            // Scroll tile into view (mobile)
            if (window.innerWidth <= 768) {
                const rect = tile.getBoundingClientRect();
                const headerOffset = 80;
                const scrollTop = window.scrollY + rect.top - headerOffset;

                window.scrollTo({
                    top: scrollTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Reset button text when tile is deactivated
    tiles.forEach(tile => {
        tile.addEventListener('transitionend', () => {
            const btn = tile.querySelector('.tile-toggle');
            if (btn) {
                btn.textContent = tile.classList.contains('active') ? 'Close' : 'Learn More';
            }
        });
    });
});
