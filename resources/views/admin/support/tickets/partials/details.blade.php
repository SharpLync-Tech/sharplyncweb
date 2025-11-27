@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<div class="card shadow-sm mb-3 admin-ticket-details-card">
    <div class="card-header bg-white border-0 pb-0">
        <h6 class="mb-1 fw-semibold">Ticket Details</h6>
        <p class="text-muted small mb-0">
            Quick overview of the ticket and customer.
        </p>
    </div>
    <div class="card-body">

        {{-- Customer --}}
        <div class="mb-3">
            <span class="small text-muted text-uppercase d-block mb-1">Customer</span>
            <p class="mb-0 fw-semibold">
                {{ $profile->business_name ?? ($user ? $user->first_name . ' ' . $user->last_name : 'Customer') }}
            </p>
            @if($user)
                <p class="mb-0 small text-muted">{{ $user->email }}</p>
            @endif
            @if($profile?->mobile_number)
                <p class="mb-0 small text-muted">{{ $profile->mobile_number }}</p>
            @endif
        </div>

        {{-- Priority --}}
        <div class="mb-3">
            <span class="small text-muted text-uppercase d-block mb-1">Priority</span>
            <form method="POST"
                  action="{{ route('admin.support.tickets.update-priority', $ticket) }}"
                  class="d-flex flex-wrap align-items-center gap-2">
                @csrf
                @method('PATCH')

                <span class="badge priority-badge priority-{{ $ticket->priority ?? 'medium' }}">
                    {{ ucfirst($ticket->priority ?? 'medium') }}
                </span>

                <select name="priority" class="form-select form-select-sm w-auto">
                    @foreach(['low','medium','high'] as $p)
                        <option value="{{ $p }}" @selected(($ticket->priority ?? 'medium') === $p)>
                            {{ ucfirst($p) }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-outline-primary btn-sm">
                    Update
                </button>
            </form>
        </div>

        {{-- Status --}}
        <div class="mb-3">
            <span class="small text-muted text-uppercase d-block mb-1">Status</span>
            <form method="POST"
                  action="{{ route('admin.support.tickets.update-status', $ticket) }}"
                  class="d-flex flex-wrap align-items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select form-select-sm w-auto">
                    @foreach(['open','pending','resolved','closed'] as $s)
                        <option value="{{ $s }}" @selected(($ticket->status ?? 'open') === $s)>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-outline-primary btn-sm">
                    Apply
                </button>
            </form>
        </div>

        {{-- Meta --}}
        <div class="mb-0">
            <span class="small text-muted text-uppercase d-block mb-1">Meta</span>
            <dl class="small mb-0">
                <div class="d-flex justify-content-between">
                    <dt class="text-muted mb-1">Ticket ID</dt>
                    <dd class="mb-1">#{{ $ticket->id }}</dd>
                </div>
                @if($ticket->reference)
                    <div class="d-flex justify-content-between">
                        <dt class="text-muted mb-1">Reference</dt>
                        <dd class="mb-1">{{ $ticket->reference }}</dd>
                    </div>
                @endif
                <div class="d-flex justify-content-between">
                    <dt class="text-muted mb-1">Created</dt>
                    <dd class="mb-1">{{ optional($ticket->created_at)->format('d M Y, H:i') }}</dd>
                </div>
                <div class="d-flex justify-content-between">
                    <dt class="text-muted mb-1">Updated</dt>
                    <dd class="mb-1">{{ optional($ticket->updated_at)->format('d M Y, H:i') }}</dd>
                </div>
                @if($ticket->closed_at)
                    <div class="d-flex justify-content-between">
                        <dt class="text-muted mb-1">Closed</dt>
                        <dd class="mb-1">{{ \Carbon\Carbon::parse($ticket->closed_at)->format('d M Y, H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
</div>
