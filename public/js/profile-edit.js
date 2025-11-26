/* ===================================================================
   SharpLync — Edit Profile JS (Google Places + Autofill)
   Version: v1.1 (Fixed Pre-fill)
=================================================================== */

document.addEventListener("DOMContentLoaded", function () {

    console.log("Profile Edit JS Loaded");

    // Fields
    const ac = document.getElementById("address_autocomplete");
    const hidden = document.getElementById("address_line1");
    const cityEl = document.getElementById("city");
    const stateEl = document.getElementById("state");
    const pcEl = document.getElementById("postcode");
    const countryEl = document.getElementById("country");

    if (!ac) {
        console.warn("Autocomplete element missing");
        return;
    }

    // FIX: Use .text property to display the address string in the
    // gmpx-place-autocomplete component's input field.
    if (hidden.value) {
        // Wait for the custom element to be defined before trying to set a property
        customElements.whenDefined('gmpx-place-autocomplete').then(() => {
             ac.text = hidden.value;
             console.log("Restored address display:", hidden.value);
        }).catch(err => {
            console.error("Failed to define gmpx-place-autocomplete:", err);
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

            // If the country is set by Google, make sure the dropdown reflects it
            const countryValue = countryEl.value;
            const countryOption = countryEl.querySelector(`option[value="${countryValue}"]`);
            if (countryOption) {
                countryOption.selected = true;
            } else if (countryValue !== 'Australia' && countryValue !== 'New Zealand') {
                // Select 'Other' if the resolved country is neither Australia nor New Zealand
                countryEl.querySelector('option[value="Other"]').selected = true;
            }


            console.log("Autofill Complete");
        });
    });

});