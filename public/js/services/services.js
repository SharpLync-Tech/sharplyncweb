/* ============================================================
   SERVICES PAGE – INTERACTIVE TILE LOGIC
   Clean version: expand in place on desktop,
   slight auto-scroll on mobile.
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const tiles = Array.from(document.querySelectorAll('.service-tile'));
    const container = document.querySelector('.services-cards');

    if (!tiles.length || !container) return;

    tiles.forEach(tile => {
        const toggleBtn = tile.querySelector('.tile-toggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
    const alreadyActive = tile.classList.contains('active');

    tiles.forEach(t => t.classList.remove('active'));
    container.classList.remove('focus-one');

    if (alreadyActive) return;

    tile.classList.add('active');
    container.classList.add('focus-one');

    // mobile only
    if (window.innerWidth <= 768) {
        const rect = tile.getBoundingClientRect();
        window.scrollTo({
            top: window.scrollY + rect.top - 80,
            behavior: "smooth"
        });
    }
});


    // Safety: keep button label in sync after transitions
    tiles.forEach(tile => {
        tile.addEventListener('transitionend', () => {
            const btn = tile.querySelector('.tile-toggle');
            if (!btn) return;
            btn.textContent = tile.classList.contains('active') ? 'Close' : 'Learn More';
        });
    });
});
