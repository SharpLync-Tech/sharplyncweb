window.ThreatCheckDragDrop = function () {
    const overlay = document.getElementById("drop-overlay");
    const fileInput = document.querySelector('input[type="file"]');

    if (!overlay || !fileInput) return;

    let dragCounter = 0;

    const showOverlay = () => overlay.classList.add("active");
    const hideOverlay = () => overlay.classList.remove("active");

    document.addEventListener("dragenter", (e) => {
        e.preventDefault();
        dragCounter++;
        showOverlay();
    });

    document.addEventListener("dragleave", (e) => {
        e.preventDefault();
        dragCounter--;
        if (dragCounter <= 0) hideOverlay();
    });

    document.addEventListener("dragover", (e) => {
        e.preventDefault();
    });

    document.addEventListener("drop", (e) => {
        e.preventDefault();
        dragCounter = 0;
        hideOverlay();

        if (!e.dataTransfer.files.length) return;

        console.log("[ThreatCheck] File dropped");

        fileInput.files = e.dataTransfer.files;
    });
};
