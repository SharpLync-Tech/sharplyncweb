document.addEventListener('DOMContentLoaded', () => {
    const tiles = Array.from(document.querySelectorAll('.service-tile'));
    const container = document.querySelector('.services-cards');

    if (!tiles.length || !container) return;

    tiles.forEach(tile => {
        const toggleBtn = tile.querySelector('.tile-toggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
            const alreadyActive = tile.classList.contains('active');

            // Clear active state from all tiles
            tiles.forEach(t => {
                t.classList.remove('active');
                t.style.removeProperty('--tile-top');
                t.style.removeProperty('--tile-left');
                t.style.removeProperty('--tile-width');
            });

            if (alreadyActive) {
                // Deactivate focus mode when closing an open tile
                container.classList.remove('focus-one');
                return;
            }

            // ----- DESKTOP BEHAVIOUR -----
            if (window.innerWidth > 768) {
                const rect = tile.getBoundingClientRect();

                // Store fixed-position coordinates (viewport-based)
                tile.style.setProperty('--tile-top', rect.top + 'px');
                tile.style.setProperty('--tile-left', rect.left + 'px');
                tile.style.setProperty('--tile-width', rect.width + 'px');
            }

            // Activate selected tile
            tile.classList.add('active');
            container.classList.add('focus-one');

            // Update button text
            toggleBtn.textContent = 'Close';

            // ----- MOBILE SCROLL INTO VIEW -----
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

    // Reset button text automatically
    tiles.forEach(tile => {
        tile.addEventListener('transitionend', () => {
            const btn = tile.querySelector('.tile-toggle');
            if (!btn) return;

            btn.textContent = tile.classList.contains('active')
                ? 'Close'
                : 'Learn More';
        });
    });
});
