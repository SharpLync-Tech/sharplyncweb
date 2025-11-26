<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Google API Debug</title>
</head>
<body>
<h1>Google Maps API Debug</h1>
<div>
    <h3>Test Autocomplete Input</h3>
    <gmpx-place-picker
        id="test_ac"
        placeholder="Start typing an address…"
    ></gmpx-place-picker>
</div>
<div>
    <h3>Formatted Address:</h3>
    <div id="formatted-output">
        (none yet)
    </div>
</div>
<div>
    <h3>Parsed Components</h3>
    <div id="components-output">
        (none yet)
    </div>
</div>
<div>
    <h3>Debug Log</h3>
    <div id="debug-log">(waiting for events)</div>
</div>

<!-- Load Maps JS API asynchronously with callback (no libraries param) -->
<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initGoogleMaps"></script>
<!-- Extended Components -->
<script type="module" src="https://unpkg.com/@googlemaps/extended-component-library@0.6.1"></script>

<script>
function log(msg) {
    console.log(msg);
    const dbg = document.getElementById("debug-log");
    dbg.textContent += msg + "\n";
}

// Define the callback function (runs after API loads)
window.initGoogleMaps = async () => {
    try {
        log("PAGE LOADED - Starting async import...");
        
        // Import the places library
        await google.maps.importLibrary('places');
        log("google.maps.places imported successfully");
        
        const ac = document.getElementById("test_ac");
        if (!ac) {
            log("ERROR: Place picker element not found");
            return;
        }
        log("<gmpx-place-picker> FOUND and ready");

        ac.addEventListener("gmpx-placechange", () => {
            const val = ac.text || "(empty)";
            log("PLACE CHANGE EVENT — Value: " + val);
            
            document.getElementById("formatted-output").innerText = val;

            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: val }, (results, status) => {
                log("GEOCODER STATUS: " + status);
                if (status !== "OK") {
                    document.getElementById("components-output").innerText = "Geocoder failed: " + status;
                    return;
                }
                let comps = results[0].address_components;
                let resultDump = "";
                comps.forEach(c => {
                    resultDump += c.types.join(", ") + ": " + c.long_name + "\n";
                });
                document.getElementById("components-output").innerText = resultDump;
                log("Address Components Parsed:\n" + resultDump);
            });
        });
    } catch (error) {
        log("ERROR during import or init: " + error.message);
    }
};
</script>
</body>
</html>