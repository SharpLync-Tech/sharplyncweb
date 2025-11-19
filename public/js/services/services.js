document.addEventListener("DOMContentLoaded", () => {

    const grid = document.getElementById("servicesGrid");
    const expanded = document.getElementById("expandedService");
    const anchor = document.getElementById("expandedAnchor");

    const expIcon = document.getElementById("expIcon");
    const expTitle = document.getElementById("expTitle");
    const expShort = document.getElementById("expShort");
    const expImage = document.getElementById("expImage");
    const expLong = document.getElementById("expLong");
    const expSubs = document.getElementById("expSubs");

    const closeBtn = document.getElementById("closeExpanded");

    // --- OPEN TILE ---
    document.querySelectorAll(".tile-toggle").forEach(btn => {
        btn.addEventListener("click", e => {

            const tile = e.target.closest(".service-tile");

            // Read tile data
            const title = tile.dataset.title;
            const short = tile.dataset.short;
            const longText = tile.dataset.long;
            const icon = tile.dataset.icon;
            const image = tile.dataset.image;
            const subs = JSON.parse(tile.dataset.subs);

            // Fill expanded layout
            expIcon.src = icon;
            expTitle.textContent = title;
            expShort.textContent = short;
            expImage.src = image;
            expLong.textContent = longText;

            expSubs.innerHTML = "";
            subs.forEach(s => {
                const li = document.createElement("li");
                li.textContent = s;
                expSubs.appendChild(li);
            });

            // Insert the expanded card right after the tile
            tile.insertAdjacentElement("afterend", expanded);

            expanded.style.display = "block";
            expanded.scrollIntoView({ behavior: "smooth" });
        });
    });

    // --- CLOSE EXPANDED ---
    closeBtn.addEventListener("click", () => {
        expanded.style.display = "none";

        // move expanded card back to anchor below grid
        anchor.insertAdjacentElement("afterend", expanded);

        window.scrollTo({ top: 0, behavior: "smooth" });
    });

});
