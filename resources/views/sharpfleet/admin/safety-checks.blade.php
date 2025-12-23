@extends('layouts.sharpfleet')

@section('title', 'Safety Checks')

@section('sharpfleet-content')

<div style="max-width:800px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Safety Checks</h1>

    <p style="margin-bottom:24px;color:#6b7280;">
        Define the pre-drive safety checks required by your organisation.
    </p>

    @if (session('success'))
        <div style="background:#dcfce7;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:24px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/safety-checks') }}">
        @csrf

        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <label style="display:block;font-weight:600;margin-bottom:12px;">
                <input type="checkbox" name="enabled" value="1" {{ $enabled ? 'checked' : '' }}>
                Enable safety checks before trips
            </label>

            <h3 style="margin:16px 0;">Safety check items</h3>

            <div id="items">
                @forelse ($items as $index => $item)
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <input type="text"
                               name="items[{{ $index }}][label]"
                               value="{{ $item['label'] }}"
                               style="flex:1;padding:10px;">
                        <button type="button" onclick="this.parentElement.remove()"
                                style="background:#fee2e2;color:#7f1d1d;border:none;padding:10px 12px;border-radius:6px;">
                            ✕
                        </button>
                    </div>
                @empty
                    <p style="color:#9ca3af;font-style:italic;">
                        No safety checks defined.
                    </p>
                @endforelse
            </div>

            <button type="button" onclick="addItem()"
                    style="margin-top:12px;background:#e5e7eb;color:#111827;
                           border:none;padding:10px 14px;border-radius:6px;">
                + Add safety check
            </button>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit"
                    style="background:#2CBFAE;color:white;padding:12px 20px;border-radius:6px;border:none;">
                Save
            </button>

            <button type="submit" name="save_and_return" value="1"
                    style="background:#e5e7eb;color:#111827;padding:12px 20px;border-radius:6px;border:none;">
                Save & return to Company
            </button>
        </div>

    </form>
</div>

<script>
    let index = {{ count($items) }};

    function addItem() {
        const container = document.getElementById('items');

        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.gap = '8px';
        row.style.marginBottom = '8px';

        row.innerHTML = `
            <input type="text" name="items[${index}][label]"
                   style="flex:1;padding:10px;">
            <button type="button"
                    onclick="this.parentElement.remove()"
                    style="background:#fee2e2;color:#7f1d1d;border:none;padding:10px 12px;border-radius:6px;">
                ✕
            </button>
        `;

        container.appendChild(row);
        index++;
    }
</script>

@endsection
