toggleBtn.addEventListener('click', () => {
    const alreadyActive = tile.classList.contains('active');

    // Close all tiles first
    tiles.forEach(t => t.classList.remove('active'));

    if (alreadyActive) {
        container.classList.remove('focus-one');
        return;
    }

    // --- NEW: ensure tile is centered BEFORE expanding ---
    const rect = tile.getBoundingClientRect();
    const viewportHeight = window.innerHeight;

    // We want the tile roughly in middle 40% of screen
    const idealTop = viewportHeight * 0.30;

    const scrollOffset = rect.top - idealTop;

    // Only scroll if tile is out of optimal vertical range
    if (Math.abs(scrollOffset) > 40) {
        window.scrollBy({
            top: scrollOffset,
            behavior: "smooth"
        });
    }

    // Wait for scroll to finish, then activate
    setTimeout(() => {
        // compute new position after scroll
        const newRect = tile.getBoundingClientRect();
        tile.style.setProperty("--tile-top", `${newRect.top}px`);
        tile.style.setProperty("--tile-left", `${newRect.left}px`);
        tile.style.setProperty("--tile-width", `${newRect.width}px`);

        tile.classList.add('active');
        container.classList.add('focus-one');

        toggleBtn.textContent = "Close";
    }, 350); // slight delay to match scroll easing
});