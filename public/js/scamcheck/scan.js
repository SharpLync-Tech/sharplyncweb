window.ThreatCheckScan = function () {

    const form     = document.getElementById("scam-form");
    const formArea = document.getElementById("form-area");
    const loader   = document.getElementById("scan-loader");

    if (!form || !formArea || !loader) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        console.log("[ThreatCheck] Scan started");

        formArea.classList.add("scanning");
        loader.style.display = "block";

        setTimeout(() => {
            form.submit();
        }, 250);
    });
};


// Still global — Blade calls it directly
window.clearScamForm = function () {

    document.querySelector('textarea[name="message"]').value = "";
    document.querySelector('input[type="file"]').value = null;

    const resultBox = document.querySelector('.result-container');
    if (resultBox) resultBox.remove();

    document.getElementById('form-area')?.classList.remove("scanning");
    document.getElementById('scan-loader').style.display = "none";
    document.getElementById('clear-btn').style.display = "none";

    console.log("[ThreatCheck] Form cleared");
};
