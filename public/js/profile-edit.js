/* ===================================================================
   SharpLync — Edit Profile JS (Google Places + Autofill)
   Version: v1.0
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

    // Restore stored address into visible field
    if (hidden.value) {
        ac.value = hidden.value;
    }

    ac.addEventListener("gmpx-placechange", () => {

        const selected = ac.value || "";
        hidden.value = selected; // Save full address

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

            cityEl.value    = part("locality") || part("postal_town") || "";
            stateEl.value   = part("administrative_area_level_1") || "";
            pcEl.value      = part("postal_code") || "";
            countryEl.value = part("country") || "Australia";

            console.log("Autofill Complete");
        });
    });

});
