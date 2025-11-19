/* ============================================================
   SERVICES PAGE – INTERACTIVE TILE LOGIC
   Version: v3.1
   - Desktop: expand-in-place (grid collapses to one tile)
   - Mobile: original accordion-style expansion restored
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

            /* =====================================================
               MOBILE MODE — RESTORED ORIGINAL ACCORDION BEHAVIOUR
               ===================================================== */
            if (window.innerWidth <= 768) {

                tile.classList.add('active');
                container.classList.add('focus-one');
                toggleBtn.textContent = "Close";

                // Smooth scroll so expanded tile is visible
                const rect = tile.getBoundingClientRect();
                const headerOffset = 60;

                window.scrollTo({
                    top: window.scrollY + rect.top - headerOffset,
                    behavior: "smooth"
                });

                return; // ⛔ ESSENTIAL: Stop desktop logic from running
            }


            /* =====================================================
               DESKTOP MODE — EXPAND IN PLACE (OPTION B)
               ===================================================== */

            // Mark the clicked tile active
            tile.classList.add('active');
            container.classList.add('focus-one');
            toggleBtn.textContent = "Close";

            // Let CSS handle the layout collapse to 1 column
            // No scrolling needed — tile stays in its row naturally

        }); // END click handler

    }); // END tile loop


    /* ============================================================
       BUTTON LABEL RESET ON COLLAPSE
       ============================================================ */
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
