{{-- 
    Standard Google Autocomplete Test (Barebones)
    Purpose: To definitively check API key validity and Autocomplete interactivity 
    using the simple, built-in Google Maps JavaScript method, bypassing the 
    problematic gmpx-place-autocomplete component.
--}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Standard Autocomplete Test</title>

    <style>
        body { font-family: sans-serif; padding: 20px; }
        input[type="text"] { border: 1px solid #ccc; padding: 10px; width: 300px; display: block; margin-bottom: 20px; }
        pre { background: #eee; padding: 10px; border: 1px solid #ddd; white-space: pre-wrap; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>

    {{-- 1. LOAD GOOGLE MAPS JS API --}}
    {{-- CRITICAL: Includes `libraries=places` and the mandatory `callback=initAutocomplete` --}}
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initAutocomplete">
    </script>
</head>
<body>

<h1>Standard Google Autocomplete Test</h1>

<label for="address_input">Address Input Field:</label>
<input type="text" id="address_input" placeholder="Start typing an address (e.g., Sydney Opera House)">

<h2>Debug Log:</h2>
<pre id="debug-log">Waiting for API script to load...</pre>

<script>
// Global variables for easy access
const debugLog = document.getElementById('debug-log');
const inputElement = document.getElementById('address_input');

// --- Step 0: Logging Function ---
function log(msg, type = 'info') {
    const time = new Date().toLocaleTimeString();
    let prefix = `[${time}] ${msg}`;
    
    // Create the HTML log entry
    let logEntry = document.createElement('div');
    logEntry.innerHTML = prefix;
    
    if (type === 'success') {
        logEntry.classList.add('success');
    } else if (type === 'error') {
        logEntry.classList.add('error');
        console.error(msg);
    }
    
    debugLog.appendChild(logEntry);
    console.log(`[TEST LOG] ${msg}`);
}

// --- Step 1: Callback Function (Fires when Maps API is fully loaded) ---
// This is called automatically by the Google Maps script due to `callback=initAutocomplete`
window.initAutocomplete = function() {
    log("STATUS 1: initAutocomplete() callback fired. This proves API key is likely valid.", 'success');
    
    if (typeof google === 'undefined' || typeof google.maps.places === 'undefined') {
        log("ERROR 1: 'google.maps.places' library is missing or failed to load.", 'error');
        return;
    }
    
    log("STATUS 2: 'google.maps.places' library is available. Attempting initialization...", 'success');

    // --- Step 2: Initialize Autocomplete Object ---
    try {
        const autocomplete = new google.maps.places.Autocomplete(inputElement, {
            // Fields and component restrictions for relevance and cost control
            fields: ["formatted_address", "address_components", "name"], 
            componentRestrictions: { country: ["au", "nz"] }
        });
        
        log("STATUS 3: Autocomplete object created successfully. INPUT IS NOW ACTIVE.", 'success');

        // --- Step 3: Add Event Listener ---
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            
            if (place.formatted_address) {
                log(`EVENT: Place selected. Address: ${place.formatted_address}`, 'info');
            } else {
                log("EVENT: Place selected, but no formatted address was found.", 'error');
            }
        });

    } catch (error) {
        log(`FATAL ERROR 3: Failed to initialize Autocomplete object. Error: ${error.message}`, 'error');
    }
};

// Log initial state
log("Initial Check: Waiting for Google API script to load...");
</script>

</body>
</html>