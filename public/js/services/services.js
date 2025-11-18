/* ============================================================
   SERVICES PAGE – INTERACTIVE TILE LOGIC
   Version: v3.0
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

            // Close all tiles first
            tiles.forEach(t => t.classList.remove('active'));

            if (alreadyActive) {
                container.classList.remove('focus-one');
                return;
            }

            /* ================================================
               DESKTOP BEHAVIOR (fixed-position expansion)
               ================================================ */
            if (window.innerWidth > 768) {

                const rect = tile.getBoundingClientRect();
                const viewportHeight = window.innerHeight;

                // Ideal position: around 30% from top
                const idealTop = viewportHeight * 0.30;

                const scrollOffset = rect.top - idealTop;

                // Scroll only if necessary
                if (Math.abs(scrollOffset) > 40) {
                    window.scrollBy({
                        top: scrollOffset,
                        behavior: "smooth"
                    });
                }

                // After scroll finishes → activate + fix position
                setTimeout(() => {
                    const newRect = tile.getBoundingClientRect();

                    tile.style.setProperty("--tile-top", `${newRect.top}px`);
                    tile.style.setProperty("--tile-left", `${newRect.left}px`);
                    tile.style.setProperty("--tile-width", `${newRect.width}px`);

                    tile.classList.add('active');
                    container.classList.add('focus-one');
                    toggleBtn.textContent = "Close";
                }, 350);

            }

            /* ================================================
               MOBILE BEHAVIOR (natural flow expansion)
               ================================================ */
            else {
                tile.classList.add('active');
                container.classList.add('focus-one');
                toggleBtn.textContent = "Close";

                // Mobile auto-scroll
                const newRect = tile.getBoundingClientRect();
                const headerOffset = 60;

                window.scrollTo({
                    top: window.scrollY + newRect.top - headerOffset,
                    behavior: "smooth"
                });
            }

        }); // END click
    }); // END tiles loop


    /* ================================================
       Reset button label on collapse
       ================================================ */
    tiles.forEach(tile => {
        tile.addEventListener('transitionend', () => {
            const btn = tile.querySelector('.tile-toggle');
            if (!btn) return;
            btn.textContent = tile.classList.contains('active') ? "Close" : "Learn More";
        });
    });

});
