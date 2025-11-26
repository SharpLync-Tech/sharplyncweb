<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Google Places API Test</title>
<style>
body { font-family: sans-serif; background: #f0f4f8; padding: 20px; }
.container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
.input-group { margin-bottom: 20px; }
label { display: block; font-weight: bold; margin-bottom: 5px; color: #1e3a8a; }
#address_input { width: 100%; padding: 10px; border: 2px solid #3b82f6; border-radius: 8px; box-sizing: border-box; font-size: 16px; transition: border-color 0.3s; }
#address_input:focus { border-color: #1d4ed8; outline: none; }
.debug-area { margin-top: 20px; padding: 15px; border-radius: 8px; background: #ffebeb; border: 1px solid #f87171; color: #b91c1c; font-size: 0.9em; white-space: pre-wrap; }
.success { background: #ebfff1; border: 1px solid #10b981; color: #065f46; }
</style>

{{-- 1. LOAD GOOGLE MAPS JS API (Essential: libraries=places) --}}
{{-- Assuming GOOGLE_MAPS_API_KEY is available via Laravel config/environment --}}
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initAutocomplete">
</script>


</head>
<body>

<div class="container">
<h1>Google Places API Test</h1>
<p>This page tests if the Maps API and Places service are initializing correctly.</p>

<div class="input-group">
    <label for="address_input">Street Address (Type here):</label>
    <input type="text" id="address_input" placeholder="Start typing an address in Australia or NZ...">
</div>

<div id="debug_output" class="debug-area">
    <p><strong>Status:</strong> Awaiting API initialization...</p>
    <p><strong>Key:</strong> The key used is loaded from `env('GOOGLE_MAPS_API_KEY')`.</p>
</div>


</div>

<script>
const debugOutput = document.getElementById('debug_output');
const inputElement = document.getElementById('address_input');

// --- Utility Functions ---

function log(message, isSuccess = false) {
    let content = debugOutput.innerHTML;
    content += `&lt;p style=&quot;margin-top: 10px; color: ${isSuccess ? &#39;#065f46&#39; : &#39;#b91c1c&#39;}&quot;&gt;&lt;strong&gt;[${new Date().toLocaleTimeString()}]&lt;/strong&gt; ${message}&lt;/p&gt;`;
    debugOutput.innerHTML = content;
    if (isSuccess) {
        debugOutput.classList.remove(&#39;debug-area&#39;);
        debugOutput.classList.add(&#39;success&#39;);
    }
    console.log(`[TEST LOG] ${message}`);
}

// --- Core Autocomplete Initialization ---

// The &#39;callback=initAutocomplete&#39; in the script URL executes this function
// *after* the Google Maps JS API and Places library are fully loaded.
function initAutocomplete() {
    log(&quot;API Check: Google Maps JS API and Places Library loaded successfully.&quot;, true);

    try {
        // 1. Initialize Autocomplete on the input field
        const autocomplete = new google.maps.places.Autocomplete(inputElement, {
            componentRestrictions: { country: [&quot;au&quot;, &quot;nz&quot;] },
            fields: [&quot;formatted_address&quot;, &quot;address_components&quot;]
        });
        
        log(&quot;Autocomplete object initialized successfully.&quot;, true);

        // 2. Add Listener for Place Selection
        autocomplete.addListener(&#39;place_changed&#39;, function() {
            const place = autocomplete.getPlace();
            
            if (place.formatted_address) {
                log(`Place Selected: ${place.formatted_address}`, true);
            } else {
                log(&quot;Warning: No formatted address found for selected place.&quot;);
            }
            
            // Display all available components for debugging
            let componentsLog = &quot;--- Address Components ---\n&quot;;
            if (place.address_components) {
                place.address_components.forEach(comp =&gt; {
                    componentsLog += `Type: ${comp.types[0]}, Value: ${comp.long_name}\n`;
                });
            }
            log(componentsLog);
        });

    } catch (error) {
        log(`FATAL ERROR during initialization: ${error.message}`);
    }
}

// Fallback if the script loads before the callback is registered (less common with &#39;callback&#39;)
if (typeof google !== &#39;undefined&#39; &amp;&amp; typeof google.maps.places !== &#39;undefined&#39;) {
    // If the script already loaded before this script, call it directly
    // Note: This is usually not needed when using &#39;callback&#39;
} else {
    log(&quot;Waiting for Google API script to load...&quot;);
}

// --- Pre-fill Test (Simulate your existing data) ---
// You can set the value directly on the standard input
const simulatedAddress = &quot;728 Mt Hutt Rd&quot;;
if (simulatedAddress) {
    inputElement.value = simulatedAddress;
    log(`Pre-fill Test: Standard input pre-filled with &quot;${simulatedAddress}&quot;`);
}

// Assign the callback function globally so the Google script can find it
window.initAutocomplete = initAutocomplete;


</script>

</body>
</html>