<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Google API Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f5;
            padding: 40px;
        }
        h1 { margin-bottom: 20px; }
        .box {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        #debug-log {
            white-space: pre-wrap;
            background: #111;
            color: #0f0;
            padding: 15px;
            border-radius: 6px;
            min-height: 150px;
            font-size: 14px;
            overflow-y: auto;
        }
        gmpx-place-autocomplete {
            display: block !important;
            width: 100% !important;
            margin-top: 10px;
        }
        gmpx-place-autocomplete::part(input) {
            padding: 10px;
            border: 1px solid #aaa;
            font-size: 16px;
            width: 100%;
        }
        * {
        pointer-events: auto !important;
    }
    </style>
</head>
<body>
<h1>Google Maps API Debug</h1>
<div class="box">
    <h3>Test Autocomplete Input</h3>
    <gmpx-place-autocomplete
        id="test_ac"
        placeholder="Start typing an address…"
    ></gmpx-place-autocomplete>
</div>
<div class="box">
    <h3>Formatted Address:</h3>
    <div id="formatted-output" style="padding:8px; background:#f5f5f5; border-radius:6px;">
        (none yet)
    </div>
</div>
<div class="box">
    <h3>Parsed Components</h3>
    <div id="components-output" style="padding:8px; background:#fafafa; border-radius:6px;">
        (none yet)
    </div>
</div>
<div class="box">
    <h3>Debug Log</h3>
    <div id="debug-log">(waiting for events)</div>
</div>

<!-- Load Maps JS API asynchronously with callback -->
<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initGoogleMaps"></script>
<!-- Extended Components (keep as-is) -->
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
        
        // Import the places library (required for new Places API)
        await google.maps.importLibrary('places');
        log("google.maps.places imported successfully");
        
        const ac = document.getElementById("test_ac");
        if (!ac) {
            log("ERROR: Autocomplete element not found");
            return;
        }
        log("<gmpx-place-autocomplete> FOUND and ready");

        // Update event to 'gmp-select' (modern equivalent)
        ac.addEventListener("gmp-select", async (event) => {
            const placePrediction = event.placePrediction;
            const val = placePrediction ? placePrediction.text : "(empty)";
            log("PLACE SELECT EVENT — Value: " + val);
            
            document.getElementById("formatted-output").innerText = val;

            // Use the new Place API for details (replaces Geocoder for better accuracy)
            if (placePrediction) {
                const place = placePrediction.toPlace();
                await place.fetchFields({ fields: ['addressComponents', 'formattedAddress'] });
                
                let resultDump = place.formattedAddress + "\n";
                place.addressComponents.forEach(c => {
                    resultDump += c.types.join(", ") + ": " + c.longText + "\n";
                });
                document.getElementById("components-output").innerText = resultDump;
                log("Address Components Parsed:\n" + resultDump);
            }
        });
    } catch (error) {
        log("ERROR during import or init: " + error.message);
    }
};
</script>
</body>
</html>