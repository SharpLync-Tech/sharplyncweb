document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.service-tile').forEach(tile => {
        tile.querySelector('.tile-toggle').addEventListener('click', () => {
            tile.classList.toggle('active');
        });
    });
});
