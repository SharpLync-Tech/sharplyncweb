// public/js/services/services.js
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.service-tile').forEach(tile => {
        const toggle = tile.querySelector('.tile-toggle');
        if (!toggle) return;

        toggle.addEventListener('click', () => {
            const willOpen = !tile.classList.contains('active');

            // Toggle detail visibility
            tile.classList.toggle('active');

            // On mobile, scroll the card into view when it opens
            if (willOpen && window.innerWidth < 820) {
                setTimeout(() => {
                    tile.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 250);
            }
        });
    });
});
