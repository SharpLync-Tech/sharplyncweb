/* ============================================================
   SERVICES PAGE – INTERACTIVE TILE LOGIC (v3.2 - FIXED)
   - Fix: Added logic for the dedicated .expanded-close button.
   - Cleanup: Removed redundant textContent setting in click handler.
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const tiles = Array.from(document.querySelectorAll('.service-tile'));
    const container = document.querySelector('.services-cards');

    if (!tiles.length || !container) return;

    // ============================================================
    // 1. TILE TOGGLE (LEARN MORE BUTTON) LOGIC
    //    Handles opening and closing the card via the main toggle button
    // ============================================================
    tiles.forEach(tile => {
        const toggleBtn = tile.querySelector('.tile-toggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
            const alreadyActive = tile.classList.contains('active');

            // Close all tiles first (essential for both desktop and mobile)
            tiles.forEach(t => t.classList.remove('active'));

            // If we clicked an already-open tile, just collapse & clear focus
            if (alreadyActive) {
                container.classList.remove('focus-one');
                return;
            }

            /* ================== MOBILE (<= 768px) ================== */
            if (window.innerWidth <= 768) {
                tile.classList.add('active');
                // NO focus-one on mobile

                // Smooth scroll so the opened card is nicely in view
                const rect = tile.getBoundingClientRect();
                const headerOffset = 60; // approx header height

                window.scrollTo({
                    top: window.scrollY + rect.top - headerOffset,
                    behavior: 'smooth'
                });

                return; // ⛔ stop here – do NOT run desktop logic
            }

            /* ================== DESKTOP (> 768px) ================== */
            tile.classList.add('active');
            container.classList.add('focus-one');
        });
    });

    // ============================================================
    // 2. EXPANDED CLOSE BUTTON LOGIC (FIX FOR THE BROKEN BUTTON)
    //    Attaches the close logic to the "Close" button inside the expanded content
    // ============================================================
    const closeBtns = document.querySelectorAll('.expanded-close');

    closeBtns.forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            // Traverse up to find the specific card this button belongs to
            const activeTile = closeBtn.closest('.service-tile');
            
            if (activeTile) {
                // Remove the 'active' class from the specific tile
                activeTile.classList.remove('active');
                // Remove the 'focus-one' class from the container
                container.classList.remove('focus-one');
                // Optional: Scroll back up to the card grid location
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });


    // ============================================================
    // 3. RESET BUTTON LABEL (AFTER ANIMATION)
    //    Uses the transitionend event to ensure text update is smooth
    // ============================================================
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