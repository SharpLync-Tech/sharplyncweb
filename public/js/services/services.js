document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector('.services-cards');
    if (!grid) return;

    const tiles = Array.from(grid.querySelectorAll('.service-tile'));

    function closeAllTiles() {
        tiles.forEach(tile => tile.classList.remove('active'));
        grid.classList.remove('focus-one');
    }

    tiles.forEach(tile => {
        const btn = tile.querySelector('.tile-toggle');
        if (!btn) return;

        btn.addEventListener('click', () => {
            const isActive = tile.classList.contains('active');

            if (isActive) {
                closeAllTiles();
                return;
            }

            closeAllTiles();

            tile.classList.add('active');
            grid.classList.add('focus-one');

            // Auto-scroll on mobile and desktop
            setTimeout(() => {
                tile.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 200);
        });
    });
});
