document.addEventListener("DOMContentLoaded", () => {

    const tiles = document.querySelectorAll(".service-tile");
    const cardsContainer = document.querySelector(".services-cards");

    let activeTile = null;

    // OPEN TILE
    tiles.forEach(tile => {
        const toggleBtn = tile.querySelector(".tile-toggle");
        const closeBtn = tile.querySelector(".tile-close-btn");

        if (toggleBtn) {
            toggleBtn.addEventListener("click", () => openTile(tile));
        }
        if (closeBtn) {
            closeBtn.addEventListener("click", () => closeTile(tile));
        }
    });

    function openTile(tile) {
        if (activeTile === tile) return; // Already open

        // Close previous
        if (activeTile) {
            closeTile(activeTile);
        }

        activeTile = tile;

        // Add active state
        tile.classList.add("active");
        cardsContainer.classList.add("focus-one");

        // Lock scroll (desktop only)
        document.body.style.overflow = "hidden";

        // Scroll into view smoothly
        setTimeout(() => {
            tile.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }, 250);
    }

    // CLOSE TILE
    function closeTile(tile) {
        tile.classList.remove("active");
        cardsContainer.classList.remove("focus-one");
        activeTile = null;

        document.body.style.overflow = "";
    }

});
