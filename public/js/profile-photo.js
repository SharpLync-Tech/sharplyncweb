// public/js/profile-photo.js

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("cp-photo-modal");
    const openBtn = document.getElementById("cp-avatar-edit-btn");
    const closeBtns = modal.querySelectorAll(".cp-photo-modal-close");
    const fileInput = document.getElementById("cp-photo-file");
    const previewImg = document.getElementById("cp-photo-preview");
    const uploadBtn = document.getElementById("cp-photo-save-btn");

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // ---------------------------
    // OPEN MODAL
    // ---------------------------
    openBtn.addEventListener("click", () => {
        modal.classList.add("visible");
    });

    // ---------------------------
    // CLOSE MODAL
    // ---------------------------
    closeBtns.forEach(btn =>
        btn.addEventListener("click", () => modal.classList.remove("visible"))
    );

    // ---------------------------
    // LIVE PREVIEW
    // ---------------------------
    fileInput.addEventListener("change", function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    // ---------------------------
    // UPLOAD FILE
    // ---------------------------
    uploadBtn.addEventListener("click", () => {
        const file = fileInput.files[0];
        if (!file) return alert("Please select a photo first.");

        const formData = new FormData();
        formData.append("profile_photo", file);
        formData.append("_token", csrf);

        uploadBtn.disabled = true;
        uploadBtn.textContent = "Saving...";

        fetch("/profile/update-photo", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update avatar instantly
                    document.querySelector(".cp-avatar img")?.setAttribute("src", data.path);

                    modal.classList.remove("visible");
                } else {
                    alert("Upload failed.");
                }
            })
            .catch(() => alert("Upload error."))
            .finally(() => {
                uploadBtn.disabled = false;
                uploadBtn.textContent = "Save Photo";
            });
    });
});
