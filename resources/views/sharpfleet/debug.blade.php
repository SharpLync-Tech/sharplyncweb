<!DOCTYPE html>
<html>
<body>
    <h2>SharpFleet Debug</h2>

    <pre>
User:
{{ print_r(auth()->user(), true) }}
    </pre>

    <form id="startTrip">
        <button type="button" onclick="startTrip()">Start Trip</button>
    </form>

    <script>
        function startTrip() {
            fetch('/app/sharpfleet/trips/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    vehicle_id: 1,
                    trip_mode: 'no_client',
                    start_km: 111111
                })
            })
            .then(r => r.json())
            .then(data => alert(JSON.stringify(data)))
            .catch(err => alert(err));
        }
    </script>
</body>
</html>
