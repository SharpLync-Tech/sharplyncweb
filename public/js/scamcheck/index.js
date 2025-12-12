document.addEventListener("DOMContentLoaded", function () {

    console.log("[ThreatCheck] Initialising");

    if (typeof ThreatCheckScan === "function") {
        ThreatCheckScan();
    }

    if (typeof ThreatCheckDragDrop === "function") {
        ThreatCheckDragDrop();
    }
});
