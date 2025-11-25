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

                    {{-- Dropdown --}}
                    <div id="sms-results"
                         style="
                            position:absolute;
                            top:100%;
                            left:0;
                            width:100%;
                            background:white;
                            border:1px solid #ccc;
                            border-radius:6px;
                            display:none;
                            z-index:2000;
                         ">
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
        <div class="card mt-4 shadow-sm">
            <div class="card-body">
                <h5>API Response</h5>
                <pre class="bg-light p-3 rounded">{{ json_encode(session('response'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

</div>

{{-- Autocomplete Script --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const searchInput = document.getElementById('sms-search');
    const resultsBox  = document.getElementById('sms-results');
    const phoneInput  = document.getElementById('sms-phone');
    const nameInput   = document.getElementById('sms-name');

    let debounceTimer = null;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();

        phoneInput.value = "";  // Always reset
        nameInput.value  = "";

        if (q.length < 2) {
            resultsBox.style.display = "none";
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {

            // ✔ Correct endpoint
            fetch(`/admin/support/search-recipients?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => renderResults(data))
                .catch(err => console.error("Autocomplete error:", err));

        }, 300);
    });

    function renderResults(items) {

        if (!items.length) {
            resultsBox.style.display = "none";
            return;
        }

        resultsBox.innerHTML = "";
        resultsBox.style.display = "block";

        items.forEach(item => {

            const div = document.createElement('div');
            div.className = "autocomplete-item";
            div.textContent = item.label;

            div.style.padding = "8px 10px";
            div.style.cursor  = "pointer";
            div.style.borderBottom = "1px solid #eee";

            div.addEventListener('mouseover', () => div.style.background = "#f1f5f9");
            div.addEventListener('mouseout',  () => div.style.background = "white");

            div.addEventListener('click', () => {
                searchInput.value = item.label;
                phoneInput.value  = item.phone;
                nameInput.value   = item.name || "";

                resultsBox.style.display = "none";
            });

            resultsBox.appendChild(div);
        });
    }

    // Clicking outside closes dropdown
    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.style.display = "none";
        }
    });
});
</script>

@endsection