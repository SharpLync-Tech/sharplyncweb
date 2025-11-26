/* ====================================================
   SharpLync Profile Photo Manager (v4.0)
==================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("avatar-modal");
    const openBtn = document.getElementById("open-avatar-modal");
    const closeBtn = document.querySelector(".cp-avatar-modal-close");

    const fileInput = document.getElementById("avatar-file-input");
    const previewImg = document.getElementById("avatar-preview");
    const saveBtn = document.getElementById("avatar-save-btn");
    const removeBtn = document.getElementById("avatar-remove-btn");

    const currentAvatar = document.getElementById("current-avatar");

    /* ---------------------------------------------
       GET CURRENT SAVED PHOTO
    ---------------------------------------------- */
    function getCurrentPhoto() {
        return currentAvatar ? currentAvatar.src : "";
    }

    /* ---------------------------------------------
       RESET PREVIEW TO CURRENT SAVED PHOTO
    ---------------------------------------------- */
    function resetPreview() {
        previewImg.src = getCurrentPhoto();
        fileInput.value = "";
        saveBtn.disabled = true;
    }

    /* ---------------------------------------------
       OPEN & CLOSE MODAL
    ---------------------------------------------- */
    if (openBtn) openBtn.addEventListener("click", () => {
        modal.classList.add("cp-visible");
        resetPreview();
    });

    if (closeBtn) closeBtn.addEventListener("click", () => {
        modal.classList.remove("cp-visible");
        resetPreview();
    });

    modal.addEventListener("click", e => {
        if (e.target === modal) {
            modal.classList.remove("cp-visible");
            resetPreview();
        }
    });

    /* ---------------------------------------------
       FILE PREVIEW
    ---------------------------------------------- */
    fileInput.addEventListener("change", e => {
        const file = e.target.files[0];
        if (!file) {
            resetPreview();
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            saveBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    });

    /* ---------------------------------------------
       UPLOAD PHOTO
    ---------------------------------------------- */
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
        .then(() => location.reload());
    });

    /* ---------------------------------------------
       REMOVE PHOTO
    ---------------------------------------------- */
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
