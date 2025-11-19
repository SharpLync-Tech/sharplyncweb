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

            // Read tile data
            const title = tile.dataset.title;
            const short = tile.dataset.short;
            const longText = tile.dataset.long;
            const icon = tile.dataset.icon;
            const image = tile.dataset.image;
            const subs = JSON.parse(tile.dataset.subs);

            // Fill expanded content
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

            // Show expanded, hide grid
            grid.style.display = "none";
            expanded.style.display = "block";

            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    });

    // --- CLOSE EXPANDED ---
    closeBtn.addEventListener("click", () => {

        // Add `.closing` class for instant collapse (removes CSS transitions)
        expanded.classList.add("closing");

        expanded.style.display = "none";
        grid.style.display = "grid";

        // Remove override once layout stabilises
        setTimeout(() => {
            expanded.classList.remove("closing");
        }, 50);

        // No smooth scroll — instant back to top = faster feel
        window.scrollTo({ top: 0 });
    });

});
