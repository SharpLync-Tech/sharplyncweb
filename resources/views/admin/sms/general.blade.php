@extends('admin.layouts.admin-layout')

@section('title', 'Send General SMS')

@section('content')

<div class="container mt-4">

    <h2 class="mb-3">Send General SMS</h2>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.support.sms.general.send') }}">
                @csrf

                {{-- Search / Autocomplete --}}
                <div class="mb-3 position-relative">
                    <label class="form-label fw-bold">Search Number or Name</label>

                    <input type="text"
                           id="sms-search"
                           class="form-control"
                           placeholder="Start typing a name or number..."
                           autocomplete="off">

                    {{-- Hidden actual phone number --}}
                    <input type="hidden" name="phone" id="sms-phone">

                    {{-- Hidden optional name --}}
                    <input type="hidden" name="name" id="sms-name">

                    {{-- Dropdown - REMOVED INLINE STYLE HERE --}}
                    <div id="sms-results">
                    </div>
                </div>

                {{-- Message --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Message</label>
                    <textarea name="message"
                              class="form-control"
                              rows="3"
                              placeholder="Type your message..."
                              required>{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    Send SMS
                </button>

            </form>

        </div>
    </div>

    @if(session('response'))
    <div class="card mt-2 shadow-sm api-card">
        <div class="card-body">

            <h5 class="mb-3" style="font-weight:700; color:#0A2A4D;">
                📡 API Response
            </h5>

            @php
                $resp = session('response');
                $result = $resp['results'][0] ?? null;
            @endphp

            <div class="api-grid">
                <div><strong>Status:</strong> {{ $resp['status'] ?? 'unknown' }}</div>
                <div><strong>Total Cost:</strong> {{ $resp['total_cost'] ?? '0' }}</div>
                <div><strong>To:</strong> {{ $result['to'] ?? '' }}</div>
                <div><strong>Message ID:</strong> {{ $result['message_id'] ?? '' }}</div>
                <div><strong>Message Status:</strong> {{ $result['status'] ?? '' }}</div>
                <div><strong>Conversation:</strong> {{ $result['conversation'] ?? '' }}</div>
                <div class="full">
                    <strong>Message Sent:</strong><br>
                    <pre class="code-block">{{ $result['message'] ?? '' }}</pre>
                </div>
            </div>

        </div>
    </div>
@endif


</div>

{{-- Autocomplete Script --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    console.log("🔥 DEBUG: SMS General page JS loaded");

    const searchInput = document.getElementById('sms-search');
    const resultsBox = document.getElementById('sms-results');
    const phoneInput   = document.getElementById('sms-phone');
    const nameInput    = document.getElementById('sms-name');

    let searchTimeout = null;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        console.log("🔹 Input changed:", q);

        phoneInput.value = '';
        nameInput.value  = '';

        if (q.length < 2) {
            console.log("⛔ Too short, hiding results");
            resultsBox.style.display = 'none';
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            console.log("🚀 Fetching: /admin/support/search-recipients?q=" + q);

            fetch(`/admin/support/search-recipients?q=${encodeURIComponent(q)}`)
                .then(res => {
                    console.log("📬 Response status:", res.status);
                    return res.json();
                })
                .then(data => {
                    console.log("📦 Data received:", data);

                    // Convert to real array
                    const items = Array.isArray(data) ? data : Object.values(data);
                    console.log("🎨 Rendering results:", items);

                    showResults(items);
                });
        }, 250);
    });

    function showResults(items) {
        console.log("🔍 showResults invoked; count:", items.length);

        if (!items || items.length === 0) {
            console.log("⚠ No items to show, hiding dropdown");
            resultsBox.style.display = 'none';
            return;
        }

        resultsBox.innerHTML = '';
        resultsBox.style.display = 'block';

        items.forEach(item => {
            console.log("➕ Adding row:", item);

            const div = document.createElement('div');
            div.textContent = item.label;
            // The following inline styles are fine for basic appearance/cursor
            div.style.padding = '8px 10px';
            div.style.cursor = 'pointer';
            div.style.borderBottom = '1px solid #eee';

            div.addEventListener('mouseover', () => div.style.background = '#f1f4f8');
            div.addEventListener('mouseout',  () => div.style.background = 'white');

            div.addEventListener('click', () => {
                console.log("👉 Selected:", item);

                searchInput.value = item.label;
                phoneInput.value  = item.phone;
                nameInput.value   = item.name ?? '';

                resultsBox.style.display = 'none';
            });

            resultsBox.appendChild(div);
        });
    }

    document.addEventListener('click', function(e) {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.style.display = 'none';
        }
    });
});
</script>

<style>

 /* FIX: Make autocomplete dropdown visible inside admin layout */
.container {
    overflow: visible !important;
    position: relative;
    z-index: 1;
}

#sms-results {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: white;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 220px;
    overflow-y: auto;
    z-index: 999999 !important;
}


#sms-results div {
    padding: 10px 12px;
    cursor: pointer;
    font-size: 14px;
}

#sms-results div:hover {
    background: #e9f2ff;
}

.api-card {
    border-left: 5px solid #2CBFAE;
}

.api-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.api-grid div {
    padding: 8px 10px;
    background: #f8fafc;
    border-radius: 6px;
    font-size: 14px;
}

.api-grid .full {
    grid-column: span 2;
}

.code-block {
    background: #0A2A4D;
    color: #fff;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
    white-space: pre-wrap;

}
</style>


@endsection