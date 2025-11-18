document.addEventListener('DOMContentLoaded', () => {
    const tiles = Array.from(document.querySelectorAll('.service-tile'));

    if (!tiles.length) return;

    tiles.forEach(tile => {
        const toggleBtn = tile.querySelector('.tile-toggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
            const alreadyActive = tile.classList.contains('active');

            // Close all tiles first (auto-close behaviour)
            tiles.forEach(t => t.classList.remove('active'));

            // If the one we clicked was already open, we’re done (it just closes)
            if (alreadyActive) return;

            // Open the clicked tile
            tile.classList.add('active');

            // On mobile, scroll the tile into view so user SEES the expansion
            if (window.innerWidth <= 768) {
                const headerOffset = 80; // approx sticky header height
                const rect = tile.getBoundingClientRect();
                const scrollTop = window.scrollY + rect.top - headerOffset;

                window.scrollTo({
                    top: scrollTop,
                    behavior: 'smooth'
                });
            }
        });
    });
});
