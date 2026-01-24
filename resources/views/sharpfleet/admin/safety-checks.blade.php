@extends('layouts.sharpfleet')

@section('title', 'Safety Checks')

@section('sharpfleet-content')

<div class="max-w-800 mx-auto mt-4">
    <h1 class="page-title mb-1">Safety Checks</h1>

    <p class="page-description mb-3">
        Define the pre-drive safety checks required by your organisation.
    </p>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/safety-checks') }}">
        @csrf

        <div class="card">
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="enabled" value="1" {{ $enabled ? 'checked' : '' }}>
                    <strong>Enable safety checks before trips</strong>
                </label>
            </div>

            <h3 class="section-title">Safety check items</h3>

            <div id="items" class="mb-2">
                @forelse ($items as $index => $item)
                    <div class="safety-item-row">
                        <input type="text"
                               name="items[{{ $index }}][label]"
                               value="{{ $item['label'] }}"
                               class="form-control"
                               placeholder="e.g. Tyres OK">
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                            Remove
                        </button>
                    </div>
                @empty
                    <p class="text-muted fst-italic mb-0">No safety checks defined.</p>
                @endforelse
            </div>

            <button type="button" class="btn-sf-navy btn-sm" onclick="addItem()">
                + Add safety check
            </button>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                Save
            </button>

            <button type="submit" name="save_and_return" value="1" class="btn btn-secondary">
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
        row.className = 'safety-item-row';

        row.innerHTML = `
            <input type="text" name="items[${index}][label]" class="form-control" placeholder="e.g. Tyres OK">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Remove</button>
        `;

        container.appendChild(row);
        index++;
    }
</script>

@endsection
