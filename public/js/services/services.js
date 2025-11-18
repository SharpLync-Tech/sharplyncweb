document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tile-toggle').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tile = e.target.closest('.service-tile');
            tile.classList.toggle('active');
        });
    });
});
