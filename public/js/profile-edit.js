/* ===================================================================
    SharpLync — Edit Profile JS (Google Places + Autofill)
    Version: v1.2 (Cleaned for late loading)
=================================================================== */

console.log("Profile Edit JS Loaded (No DOMContentLoaded)");

// Fields
const ac = document.getElementById("address_autocomplete");
const hidden = document.getElementById("address_line1");
const cityEl = document.getElementById("city");
const stateEl = document.getElementById("state");
const pcEl = document.getElementById("postcode");
const countryEl = document.getElementById("country");

if (!ac) {
    console.warn("Autocomplete element missing");
} else {
    // This pre-fill logic should now be handled by the inline script
    // but remains here as a fallback in case the inline script runs too early.
    // Ensure the custom element is defined before accessing .text
    if (hidden.value) {
        customElements.whenDefined('gmpx-place-autocomplete').then(() => {
             ac.text = hidden.value;
             console.log("Restored address display:", hidden.value);
        });
    }

    ac.addEventListener("gmpx-placechange", () => {

        // When a place is selected, the component's .text property holds the display string.
        const selected = ac.text || "";
        hidden.value = selected; // Save full address string to the hidden field

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

            // Autofill the separated fields
            cityEl.value    = part("locality") || part("postal_town") || "";
            stateEl.value   = part("administrative_area_level_1") || "";
            pcEl.value      = part("postal_code") || "";
            countryEl.value = part("country") || "Australia";

            // Country Dropdown Sync
            const countryValue = countryEl.value;
            const countryOption = countryEl.querySelector(`option[value="${countryValue}"]`);
            if (countryOption) {
                countryOption.selected = true;
            } else if (countryValue !== 'Australia' && countryValue !== 'New Zealand') {
                countryEl.querySelector('option[value="Other"]').selected = true;
            }

            console.log("Autofill Complete");
        });
    });
}