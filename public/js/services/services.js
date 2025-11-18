document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.service-tile').forEach(tile => {
        const btn = tile.querySelector('.tile-toggle');
        
        btn.addEventListener('click', () => {
            tile.classList.toggle('active');

            // Only scroll when opening, not closing
            if (tile.classList.contains('active')) {
                tile.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });
});
