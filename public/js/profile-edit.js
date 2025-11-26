/* ===================================================================
    SharpLync — Edit Profile JS (Google Places + Autofill)
    Version: v1.3 (Fixed Restore Bug + Cleaned Load Order)
=================================================================== */

console.log("Profile Edit JS Loaded");

// DOM fields
const ac = document.getElementById("address_autocomplete");
const hidden = document.getElementById("address_line1");
const cityEl = document.getElementById("city");
const stateEl = document.getElementById("state");
const pcEl = document.getElementById("postcode");
const countryEl = document.getElementById("country");

// -------------------------------------------------------------
// 1. SAFELY RESTORE PREVIOUS DATA ON PAGE LOAD
// -------------------------------------------------------------
function restorePreviousValues() {
    console.log("Restoring previous profile values...");

    // Restore address into Google component
    if (hidden.value) {
        customElements.whenDefined("gmpx-place-autocomplete").then(() => {
            ac.text = hidden.value;
            console.log("Restored Google Autocomplete text:", hidden.value);
        });
    }

    // Restore all manual fields (city/state/postcode/country)
    if (cityEl.value)       console.log("City restored:", cityEl.value);
    if (stateEl.value)      console.log("State restored:", stateEl.value);
    if (pcEl.value)         console.log("Postcode restored:", pcEl.value);
    if (countryEl.value)    console.log("Country restored:", countryEl.value);
}

// Run immediately (safe because form fields already exist in DOM)
restorePreviousValues();


// -------------------------------------------------------------
// 2. HANDLE PLACE SELECTION (AUTOFILL SEPARATE FIELDS)
// -------------------------------------------------------------
if (!ac) {
    console.warn("Autocomplete element missing");
} else {

    ac.addEventListener("gmpx-placechange", () => {

        const selected = ac.text || "";
        hidden.value = selected;

        console.log("Selected Address:", selected);

        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: selected }, (results, status) => {

            if (status !== "OK" || !results[0]) {
                console.warn("Geocode failed:", status);
                return;
            }

            const comps = results[0].address_components;

            function part(type) {
                const c = comps.find(x => x.types.includes(type));
                return c ? c.long_name : "";
            }

            // Save separated values
            cityEl.value    = part("locality") || part("postal_town") || "";
            stateEl.value   = part("administrative_area_level_1") || "";
            pcEl.value      = part("postal_code") || "";
            countryEl.value = part("country") || "Australia";

            // Sync dropdown if needed
            const countryValue = countryEl.value;
            const option = countryEl.querySelector(`option[value="${countryValue}"]`);
            if (option) {
                option.selected = true;
            } else {
                countryEl.querySelector('option[value="Other"]').selected = true;
            }

            console.log("Autofill Complete:", {
                city: cityEl.value,
                state: stateEl.value,
                postcode: pcEl.value,
                country: countryEl.value
            });
        });
    });
}
