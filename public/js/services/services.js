/* ============================================================
   SERVICES PAGE – INTERACTIVE TILE LOGIC
   Version: v3.2
   - Desktop: expand-in-place (Option B) with focus-one
   - Mobile: simple stacked accordion, no focus-one effects
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

            // If we clicked an already-open tile, just collapse & clear focus
            if (alreadyActive) {
                container.classList.remove('focus-one');
                return;
            }

            /* =====================================================
               MOBILE (<= 768px) — stacked cards, no blur/zoom
               ===================================================== */
            if (window.innerWidth <= 768) {
                tile.classList.add('active');
                // 🔸 important: NO focus-one on mobile
                toggleBtn.textContent = 'Close';

                // Smooth scroll so the opened card is nicely in view
                const rect = tile.getBoundingClientRect();
                const headerOffset = 60; // approx header height

                window.scrollTo({
                    top: window.scrollY + rect.top - headerOffset,
                    behavior: 'smooth'
                });

                return; // ⛔ stop here – do NOT run desktop logic
            }

            /* =====================================================
               DESKTOP (> 768px) — Option B expand-in-place
               ===================================================== */
            tile.classList.add('active');
            container.classList.add('focus-one');
            toggleBtn.textContent = 'Close';
        });
    });

    /* ============================================================
       Reset button label when card finishes animating
       ============================================================ */
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
