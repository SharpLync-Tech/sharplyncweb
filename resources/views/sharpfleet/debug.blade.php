@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Debug')

@section('sharpfleet-content')
<div class="max-w-800 mx-auto mt-4">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">SharpFleet Debug</h2>
        </div>

        <p class="text-muted mb-3">Developer utility page.</p>

        <pre class="mb-3 pre-wrap">User:
{{ print_r(auth()->user(), true) }}
        </pre>

        <pre id="sfDebugOutput" class="mb-3 pre-wrap"></pre>

        <button type="button" class="btn btn-primary" onclick="startTrip()">Start Trip</button>
    </div>

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
                    trip_mode: 'business',
                    start_km: 111111
                })
            })
            .then(r => r.json())
            .then(data => {
                if (window.SharpFleetModal && typeof window.SharpFleetModal.notice === 'function') {
                    window.SharpFleetModal.notice({
                        title: 'Debug response',
                        message: JSON.stringify(data)
                    });
                    return;
                }

                // Fallback (non-modal): render to the page.
                const pre = document.getElementById('sfDebugOutput');
                if (pre) pre.textContent = JSON.stringify(data, null, 2);
            })
            .catch(err => {
                const message = (err && err.message) ? err.message : String(err);
                if (window.SharpFleetModal && typeof window.SharpFleetModal.notice === 'function') {
                    window.SharpFleetModal.notice({
                        title: 'Debug error',
                        message
                    });
                    return;
                }

                const pre = document.getElementById('sfDebugOutput');
                if (pre) pre.textContent = message;
            });
        }
    </script>
</div>
@endsection
