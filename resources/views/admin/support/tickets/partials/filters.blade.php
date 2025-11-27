<form method="GET"
      action="{{ route('admin.support.tickets.index') }}"
      class="row gy-2 gx-2 align-items-end admin-ticket-filters">

    {{-- Search --}}
    <div class="col-12 col-md-4">
        <label class="form-label small fw-semibold mb-1">Search</label>
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               class="form-control form-control-sm"
               placeholder="Subject, reference, customer, email, phone">
    </div>

    {{-- Status --}}
    <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">All</option>
            @foreach(['open' => 'Open', 'pending' => 'Pending', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Priority --}}
    <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold mb-1">Priority</label>
        <select name="priority" class="form-select form-select-sm">
            <option value="">All</option>
            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $value => $label)
                <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Sort --}}
    <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold mb-1">Sort</label>
        <select name="sort" class="form-select form-select-sm">
            <option value="">Most recent</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
            <option value="priority" @selected(request('sort') === 'priority')>Priority</option>
        </select>
    </div>

    {{-- Buttons --}}
    <div class="col-6 col-md-2 text-end">
        <button type="submit" class="btn btn-teal btn-sm w-100 mb-1">
            <i class="bi bi-funnel me-1"></i> Apply
        </button>
        <a href="{{ route('admin.support.tickets.index') }}"
           class="btn btn-outline-secondary btn-sm w-100">
            Reset
        </a>
    </div>
</form>
