<script>
/* === Mobile Menu Toggle === */
function toggleMenu() {
    const nav = document.getElementById('mobileNav');
    const overlay = document.getElementById('overlay');
    nav.classList.toggle('active');
    overlay.classList.toggle('active');
}

/* === Modal Controls === */
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    // Add active class
    modal.classList.add('active');

    // Create independent modal overlay
    let modalOverlay = document.getElementById('modalOverlay');
    if (!modalOverlay) {
        modalOverlay = document.createElement('div');
        modalOverlay.id = 'modalOverlay';
        modalOverlay.className = 'overlay active';
        modalOverlay.style.zIndex = 2900;
        modalOverlay.onclick = () => closeModal(id);
        document.body.appendChild(modalOverlay);
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const modalOverlay = document.getElementById('modalOverlay');
    if (modal) modal.classList.remove('active');
    if (modalOverlay) modalOverlay.remove();
}
</script>
