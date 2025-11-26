{{-- 
    Google Maps Debug Page
    Purpose: Test API loading, Autocomplete visibility, geocoder results
    NOTE: This is a raw standalone debug template.
--}}

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

{{-- GOOGLE MAPS JS --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&v=weekly"></script>

{{-- EXTENDED COMPONENTS --}}
<script type="module" src="https://unpkg.com/@googlemaps/extended-component-library@0.6.1"></script>

<script>
function log(msg) {
    console.log(msg);
    const dbg = document.getElementById("debug-log");
    dbg.textContent += msg + "\n";
}

window.addEventListener("load", function() {
    log("PAGE LOADED");

    if (!google || !google.maps) {
        log("ERROR: google.maps NOT loaded");
        return;
    }

    log("google.maps loaded successfully");
    log("Maps JS Version: " + google.maps.version);

    const ac = document.getElementById("test_ac");

    if (!ac) {
        log("ERROR: Autocomplete element not found");
        return;
    }

    log("<gmpx-place-autocomplete> FOUND and ready");

    ac.addEventListener("gmpx-placechange", () => {
        const val = ac.value || "(empty)";
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

});
</script>

</body>
</html>
