document.addEventListener("DOMContentLoaded", () => {

    const grid = document.getElementById("servicesGrid");
    const expanded = document.getElementById("expandedService");

    const expIcon = document.getElementById("expIcon");
    const expTitle = document.getElementById("expTitle");
    const expShort = document.getElementById("expShort");
    const expImage = document.getElementById("expImage");
    const expLong = document.getElementById("expLong");
    const expSubs = document.getElementById("expSubs");

    // ⭐ Partner Badge Elements
    const partnerBadge = document.getElementById("partnerBadge");
    const partnerBadgeLogo = document.getElementById("partnerBadgeLogo");
    const partnerBadgeTitle = document.getElementById("partnerBadgeTitle");
    const partnerBadgeText = document.getElementById("partnerBadgeText");

    const closeBtn = document.getElementById("closeExpanded");

    // =========================
    // OPEN CARD
    // =========================
    document.querySelectorAll(".tile-toggle").forEach(btn => {
        btn.addEventListener("click", e => {

            const tile = e.target.closest(".service-tile");

            expTitle.textContent = tile.dataset.title;
            expShort.textContent = tile.dataset.short;
            expLong.textContent = tile.dataset.long;
            expIcon.src = tile.dataset.icon;
            expImage.src = tile.dataset.image;

            // -------------------------
            // ⭐ TREND MICRO BADGE LOGIC
            // -------------------------
            if (tile.dataset.partnerLogo) {
                partnerBadge.style.display = "flex";
                partnerBadgeLogo.src = tile.dataset.partnerLogo;
                partnerBadgeTitle.textContent = tile.dataset.partnerTitle;
                partnerBadgeText.textContent = tile.dataset.partnerText;
            } else {
                partnerBadge.style.display = "none";
            }

            // Included services
            expSubs.innerHTML = "";
            JSON.parse(tile.dataset.subs).forEach(text => {
                const li = document.createElement("li");
                li.textContent = text;
                li.classList.add("arrow-animate"); // <-- arrow animation
                expSubs.appendChild(li);
            });

            grid.classList.add("hidden");
            expanded.style.display = "block";

            // ==== window.scrollTo({ top: 0 }); /// Removed by Jannie ====
            expanded.scrollIntoView({ behavior: "instant", block: "start" }); // ==== Added by Jannie ====

        });
    });

    // =========================
    // CLOSE CARD – INSTANT
    // =========================
    closeBtn.addEventListener("click", () => {
        expanded.style.display = "none";
        grid.classList.remove("hidden");
        // ==== window.scrollTo({ top: 0 }); Removed by Jannie ====
        expanded.scrollIntoView({ behavior: "instant", block: "start" }); // ==== Added by Jannie ====
    });

});
