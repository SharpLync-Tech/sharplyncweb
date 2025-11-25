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

    <div class="card shadow-sm mb-4">
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

                    {{-- Dropdown --}}
                    <div id="sms-results"></div>
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

    {{-- =======================
        API RESPONSE PANEL
    ======================== --}}
    @if(session('response'))
        @php
            $resp = session('response');
            $r = $resp['results'][0] ?? [];
            $status = strtolower($r['status'] ?? 'unknown');
            $statusColor = $status === 'success' ? '#2CBFAE' : '#E74C3C';
        @endphp

        <div class="card mt-4 shadow api-card">
            <div class="card-body">

                {{-- HEADER --}}
                <div class="api-header">
                    <div class="api-header-left">
                        <div class="api-icon success-pulse">📨</div>
                        <h4 class="api-title">SMS Sent</h4>
                    </div>
                    <span class="api-time">{{ now()->format('d M Y - h:i A') }}</span>
                </div>

                {{-- GRID --}}
                <div class="api-grid">
                    <div>
                        <strong>Status:</strong>
                        <span class="badge-status" style="background:{{ $statusColor }}">{{ ucfirst($status) }}</span>
                    </div>

                    <div>
                        <strong>Total Cost:</strong>
                        ${{ $resp['total_cost'] ?? '0' }}
                    </div>

                    <div>
                        <strong>To:</strong>
                        {{ $r['to'] ?? '-' }}
                    </div>

                    <div class="msg-id-row">
                        <strong>Message ID:</strong>
                        <span>{{ $r['message_id'] ?? '-' }}</span>

                        @if(!empty($r['message_id']))
                            <button class="copy-btn"
                                    data-copy="{{ $r['message_id'] }}">
                                Copy
                            </button>
                        @endif
                    </div>

                    <div>
                        <strong>Conversation:</strong>
                        {{ $r['conversation'] ?? '0' }}
                    </div>

                    <div class="full">
                        <strong>Message Sent:</strong>
                        <pre class="code-block">{{ $r['message'] ?? '' }}</pre>
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
    const resultsBox  = document.getElementById('sms-results');
    const phoneInput  = document.getElementById('sms-phone');
    const nameInput   = document.getElementById('sms-name');

    let searchTimeout = null;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        phoneInput.value = '';
        nameInput.value = '';

        if (q.length < 2) {
            resultsBox.style.display = 'none';
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetch(`/admin/support/search-recipients?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    const items = Array.isArray(data) ? data : Object.values(data);
                    showResults(items);
                });
        }, 200);
    });

    function showResults(items) {
        if (!items.length) {
            resultsBox.style.display = 'none';
            return;
        }

        resultsBox.innerHTML = '';
        resultsBox.style.display = 'block';

        items.forEach(item => {
            const div = document.createElement('div');
            div.textContent = item.label;
            div.className = 'dropdown-item';

            div.addEventListener('click', () => {
                searchInput.value = item.label;
                phoneInput.value = item.phone;
                nameInput.value = item.name ?? '';
                resultsBox.style.display = 'none';
            });

            resultsBox.appendChild(div);
        });
    }

    document.addEventListener('click', e => {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.style.display = 'none';
        }
    });

    // Copy Message-ID
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.clipboard.writeText(btn.dataset.copy);
            btn.textContent = 'Copied!';
            setTimeout(() => (btn.textContent = 'Copy'), 1500);
        });
    });
});
</script>

<style>

.container { overflow: visible !important; position: relative; z-index: 1; }

/* AUTOCOMPLETE */
#sms-results {
    position:absolute;
    top:100%;
    left:0;
    width:100%;
    background:#fff;
    border:1px solid #ccc;
    border-radius:6px;
    box-shadow:0 4px 12px rgba(0,0,0,.15);
    max-height:220px;
    overflow-y:auto;
    display:none;
    z-index:99999;
}
#sms-results .dropdown-item {
    padding:10px 12px;
    cursor:pointer;
    font-size:14px;
    border-bottom:1px solid #eee;
}
#sms-results .dropdown-item:hover { background:#e9f2ff; }

/* API CARD */
.api-card { border-left:6px solid #2CBFAE; }

/* HEADER */
.api-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:12px;
}
.api-header-left { display:flex; align-items:center; gap:10px; }
.api-title { font-weight:700; color:#0A2A4D; margin:0; }
.api-time { font-size:13px; color:#6b7a89; }

/* Animated pulsing icon */
.success-pulse {
    font-size:32px;
    animation:pulse 1.4s infinite ease-in-out;
}
@keyframes pulse {
    0% { transform:scale(1); opacity:.8; }
    50% { transform:scale(1.15); opacity:1; }
    100% { transform:scale(1); opacity:.8; }
}

/* GRID */
.api-grid {
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:12px;
}
.api-grid > div {
    background:#f8fafc;
    padding:10px;
    border-radius:6px;
    font-size:14px;
}
.api-grid .full { grid-column:span 2; }

/* STATUS BADGE */
.badge-status {
    padding:3px 8px;
    border-radius:4px;
    color:#fff;
    font-weight:600;
    font-size:12px;
}

/* COPY BUTTON */
.copy-btn {
    margin-left:10px;
    padding:3px 8px;
    font-size:12px;
    background:#0A2A4D;
    color:#fff;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
.copy-btn:hover { background:#104976; }

/* MESSAGE BLOCK */
.code-block {
    background:#0A2A4D;
    color:#fff;
    padding:12px;
    border-radius:6px;
    white-space:pre-wrap;
}

/* MOBILE */
@media(max-width:768px){
    .api-grid { grid-template-columns:1fr; }
    .api-grid .full { grid-column:1; }
    .api-header { flex-direction:column; align-items:flex-start; gap:4px; }
}
</style>

@endsection
