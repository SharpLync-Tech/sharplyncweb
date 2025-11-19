document.addEventListener("DOMContentLoaded", () => {

    const grid = document.getElementById("servicesGrid");
    const expanded = document.getElementById("expandedService");

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

            // Read tile data attributes
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

            // Populate subs
            expSubs.innerHTML = "";
            subs.forEach(s => {
                const li = document.createElement("li");
                li.textContent = s;
                expSubs.appendChild(li);
            });

            // Hide grid, show expanded
            grid.style.display = "none";
            expanded.style.display = "block";

            // INSTANT scroll to top
            window.scrollTo(0, 0);
        });
    });

    // --- CLOSE EXPANDED ---
    closeBtn.addEventListener("click", () => {

        expanded.style.display = "none";
        grid.style.display = "grid";

        // INSTANT scroll to top
        window.scrollTo(0, 0);
    });

});
