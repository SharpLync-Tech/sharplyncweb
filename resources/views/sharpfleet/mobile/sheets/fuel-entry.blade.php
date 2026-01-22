{{-- =========================================================
     SharpFleet Mobile - Add Fuel (Testing)
========================================================= --}}

<div
    id="sf-sheet-fuel-entry"
    class="sf-sheet"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="sf-fuel-entry-title"
>
    <div class="sf-sheet-header">
        <h2 id="sf-fuel-entry-title">Add Fuel</h2>

        <button
            type="button"
            class="sf-sheet-close"
            data-sheet-close
            aria-label="Close"
        >
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
        <div id="sfFuelStatus" class="hint-text" style="margin-bottom: 10px; display: none;"></div>

        <form id="fuelEntryForm" action="#" onsubmit="return false;">
            <div class="form-group">
                <label class="form-label">Vehicle</label>
                <select name="vehicle_id" class="form-control" required>
                    @foreach ($vehicles as $vehicle)
                        @php
                            $selectedVehicleId = (int) ($activeTrip->vehicle_id ?? 0);
                            $vehicleId = (int) ($vehicle->id ?? 0);
                        @endphp
                        <option value="{{ $vehicle->id }}" {{ $vehicleId === $selectedVehicleId ? 'selected' : '' }}>
                            {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Odometer reading</label>
                <input type="number" name="odometer_reading" class="form-control" inputmode="numeric" placeholder="e.g. 124600">
            </div>

            <div class="form-group">
                <label class="form-label">Receipt</label>
                <div style="margin-bottom: 10px;">
                    <video id="sfFuelVideo" autoplay playsinline style="width: 100%; border-radius: 12px; border: 1px solid rgba(255,255,255,0.16); display: none;"></video>
                    <canvas id="sfFuelCanvas" style="display: none;"></canvas>
                    <img id="sfFuelPreview" alt="Receipt preview" style="width: 100%; border-radius: 12px; border: 1px solid rgba(255,255,255,0.16); display: none;">
                </div>

                <div class="btn-group" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="sf-mobile-secondary-btn" id="sfFuelStartBtn" style="flex: 1; min-width: 140px;">
                        Open Camera
                    </button>
                    <button type="button" class="sf-mobile-primary-btn" id="sfFuelCaptureBtn" style="flex: 1; min-width: 140px; display: none;">
                        Capture Photo
                    </button>
                    <button type="button" class="sf-mobile-secondary-btn" id="sfFuelRetakeBtn" style="flex: 1; min-width: 140px; display: none;">
                        Retake
                    </button>
                </div>

                <div class="hint-text" style="margin-top: 8px;">
                    Tip: Use good lighting and keep the receipt flat for best results.
                </div>

                <input type="file" name="receipt_file" id="sfFuelFileInput" class="form-control" accept="image/*" capture="environment" style="margin-top: 10px;">
            </div>

            <button type="submit" class="sf-mobile-primary-btn" id="sfFuelSubmitBtn" style="margin-top: 12px;" disabled>
                Save Fuel Receipt
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const sheet = document.getElementById('sf-sheet-fuel-entry');
    if (!sheet) return;

    const openBtn = document.querySelector('[data-sheet-open="fuel-entry"]');
    const closeBtns = sheet.querySelectorAll('[data-sheet-close]');
    const backdrop = document.getElementById('sf-sheet-backdrop');

    const video = document.getElementById('sfFuelVideo');
    const canvas = document.getElementById('sfFuelCanvas');
    const preview = document.getElementById('sfFuelPreview');
    const startBtn = document.getElementById('sfFuelStartBtn');
    const captureBtn = document.getElementById('sfFuelCaptureBtn');
    const retakeBtn = document.getElementById('sfFuelRetakeBtn');
    const fileInput = document.getElementById('sfFuelFileInput');
    const form = document.getElementById('fuelEntryForm');
    const submitBtn = document.getElementById('sfFuelSubmitBtn');
    const status = document.getElementById('sfFuelStatus');

    let stream = null;
    let capturedBlob = null;

    function setStatus(message, isError) {
        if (!status) return;
        status.textContent = message || '';
        status.style.display = message ? '' : 'none';
        status.style.color = isError ? '#ffb3b3' : '#bfeee8';
    }

    function resetPreview() {
        capturedBlob = null;
        if (preview) {
            preview.src = '';
            preview.style.display = 'none';
        }
        if (video) {
            video.style.display = 'none';
        }
        if (captureBtn) captureBtn.style.display = 'none';
        if (retakeBtn) retakeBtn.style.display = 'none';
        if (submitBtn) submitBtn.disabled = true;
    }

    async function startCamera() {
        resetPreview();
        setStatus('');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Camera not available. Use file upload instead.', true);
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
            if (video) {
                video.srcObject = stream;
                video.style.display = '';
            }
            if (captureBtn) captureBtn.style.display = '';
            if (retakeBtn) retakeBtn.style.display = 'none';
        } catch (e) {
            setStatus('Camera permission denied. Use file upload instead.', true);
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        if (video) {
            video.srcObject = null;
            video.style.display = 'none';
        }
    }

    function capturePhoto() {
        if (!video || !canvas) return;
        if (!video.videoWidth || !video.videoHeight) return;

        const maxSize = 1600;
        const width = video.videoWidth;
        const height = video.videoHeight;
        const scale = Math.min(1, maxSize / Math.max(width, height));
        const targetW = Math.round(width * scale);
        const targetH = Math.round(height * scale);

        canvas.width = targetW;
        canvas.height = targetH;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, targetW, targetH);

        canvas.toBlob((blob) => {
            if (!blob) {
                setStatus('Could not capture photo. Try again.', true);
                return;
            }
            capturedBlob = blob;
            if (preview) {
                preview.src = URL.createObjectURL(blob);
                preview.style.display = '';
            }
            stopCamera();
            if (captureBtn) captureBtn.style.display = 'none';
            if (retakeBtn) retakeBtn.style.display = '';
            if (submitBtn) submitBtn.disabled = false;
            setStatus('Photo captured. Review and save.', false);
        }, 'image/jpeg', 0.82);
    }

    function handleFileInput() {
        if (!fileInput || !submitBtn) return;
        if (fileInput.files && fileInput.files[0]) {
            capturedBlob = null;
            if (preview) {
                preview.src = URL.createObjectURL(fileInput.files[0]);
                preview.style.display = '';
            }
            submitBtn.disabled = false;
            setStatus('File ready. Save to upload.', false);
        }
    }

    async function submitFuelEntry() {
        if (!form) return;
        if (!navigator.onLine) {
            setStatus('You are offline. Please try again when online.', true);
            return;
        }

        const fd = new FormData(form);
        fd.delete('receipt_file');

        if (capturedBlob) {
            fd.append('receipt', capturedBlob, 'receipt.jpg');
        } else if (fileInput && fileInput.files && fileInput.files[0]) {
            fd.append('receipt', fileInput.files[0]);
        } else {
            setStatus('Capture a receipt photo before saving.', true);
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            submitBtn.disabled = true;
            const res = await fetch('/app/sharpfleet/mobile/fuel', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: fd,
            });

            if (!res.ok) {
                let msg = 'Could not save fuel receipt.';
                try {
                    const data = await res.json();
                    if (data && data.message) msg = data.message;
                } catch (e) {}
                setStatus(msg, true);
                submitBtn.disabled = false;
                return;
            }

            setStatus('Fuel receipt saved and emailed.', false);
            form.reset();
            resetPreview();
            if (fileInput) fileInput.value = '';
            setTimeout(() => {
                const closeBtn = sheet.querySelector('[data-sheet-close]');
                if (closeBtn) closeBtn.click();
                setStatus('');
            }, 800);
        } catch (e) {
            setStatus('Network error while uploading receipt.', true);
            submitBtn.disabled = false;
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', () => {
            setTimeout(() => {
                if (startBtn) startBtn.click();
            }, 150);
        });
    }

    if (startBtn) startBtn.addEventListener('click', startCamera);
    if (captureBtn) captureBtn.addEventListener('click', capturePhoto);
    if (retakeBtn) retakeBtn.addEventListener('click', startCamera);
    if (fileInput) fileInput.addEventListener('change', handleFileInput);
    if (form) form.addEventListener('submit', (e) => {
        e.preventDefault();
        submitFuelEntry();
    });

    closeBtns.forEach(btn => btn.addEventListener('click', stopCamera));
    if (backdrop) {
        backdrop.addEventListener('click', stopCamera);
    }
})();
</script>
