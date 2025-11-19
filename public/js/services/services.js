/* ============================================================
   SERVICES TILE INTERACTIONS – v3.1 (Expand In Place)
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

            // Close all
            tiles.forEach(t => t.classList.remove('active'));
            container.classList.remove('focus-one');

            if (alreadyActive) return;

            // Activate selected
            tile.classList.add('active');
            container.classList.add('focus-one');

            toggleBtn.textContent = "Close";

            /* MOBILE AUTOSCROLL */
            if (window.innerWidth <= 768) {
                const rect = tile.getBoundingClientRect();
                window.scrollTo({
                    top: window.scrollY + rect.top - 70,
                    behavior: "smooth"
                });
            }

        });
    });

    /* Restore button text on collapse */
    tiles.forEach(tile => {
        tile.addEventListener('transitionend', () => {
            const btn = tile.querySelector('.tile-toggle');
            if (!btn) return;
            btn.textContent = tile.classList.contains('active')
                ? "Close"
                : "Learn More";
        });
    });

});
