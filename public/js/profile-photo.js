/* ====================================================
   SharpLync Profile Photo Manager
   Version 1.1 — Stable Modal + Preview + Save + Remove
==================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("avatar-modal");
    const openBtn = document.getElementById("open-avatar-modal");
    const closeBtn = document.querySelector(".cp-avatar-modal-close");

    const fileInput = document.getElementById("avatar-file-input");
    const previewImg = document.getElementById("avatar-preview");
    const saveBtn = document.getElementById("avatar-save-btn");
    const removeBtn = document.getElementById("avatar-remove-btn");

    /* ------------------------------------------------
       SAFETY: If modal elements missing, abort
    -------------------------------------------------- */
    if (!modal || !openBtn || !fileInput) {
        console.warn("[Avatar] Modal elements missing.");
        return;
    }


    /* ------------------------------------------------
       OPEN & CLOSE MODAL
    -------------------------------------------------- */
    openBtn.addEventListener("click", () => {
        modal.classList.add("cp-visible");
        document.body.style.overflow = "hidden"; // prevent background scroll
    });

    closeBtn.addEventListener("click", closeModal);

    modal.addEventListener("click", e => {
        if (e.target === modal) closeModal();
    });

    function closeModal() {
        modal.classList.remove("cp-visible");
        document.body.style.overflow = "";
        resetPreview();
    }


    /* ------------------------------------------------
       FILE PREVIEW
    -------------------------------------------------- */
    function resetPreview() {
        previewImg.src = "";
        saveBtn.disabled = true;
        fileInput.value = "";
    }

    fileInput.addEventListener("change", e => {
        const file = e.target.files[0];
        if (!file) return resetPreview();

        const reader = new FileReader();
        reader.onload = ev => {
            previewImg.src = ev.target.result;
            saveBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    });


    /* ------------------------------------------------
       UPLOAD PHOTO
    -------------------------------------------------- */
    saveBtn.addEventListener("click", () => {

        const file = fileInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append("profile_photo", file);
        formData.append("_token", document.querySelector("meta[name='csrf-token']").content);

        fetch("/profile/update-photo", {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(() => location.reload());
    });


    /* ------------------------------------------------
       REMOVE PHOTO
    -------------------------------------------------- */
    removeBtn.addEventListener("click", () => {

        fetch("/profile/remove-photo", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            }
        })
        .then(() => location.reload());
    });

});
