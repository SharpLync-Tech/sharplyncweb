document.addEventListener('DOMContentLoaded', () => {
    const tiles = Array.from(document.querySelectorAll('.service-tile'));

    if (!tiles.length) return;

    tiles.forEach(tile => {
        const toggleBtn = tile.querySelector('.tile-toggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
            const isOpen = tile.classList.contains('active');

            // Close ALL tiles
            tiles.forEach(t => {
                t.classList.remove('active');
                const btn = t.querySelector('.tile-toggle');
                if (btn) btn.textContent = "Learn More";
            });

            // If the clicked tile was open, it now closes
            if (isOpen) return;

            // Otherwise, open it
            tile.classList.add('active');
            toggleBtn.textContent = "Close";

            // Mobile scroll into view
            if (window.innerWidth <= 768) {
                const headerOffset = 80;
                const rect = tile.getBoundingClientRect();
                const scrollTop = window.scrollY + rect.top - headerOffset;

                window.scrollTo({
                    top: scrollTop,
                    behavior: 'smooth'
                });
            }
        });
    });
});
