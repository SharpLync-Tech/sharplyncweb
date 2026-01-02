@extends('admin.layouts.admin-layout')

@section('title', 'SharpFleet Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">SharpFleet Audit Logs</h2>
            <div class="sl-subtitle small">Subscriber actions (tenant) and platform admin actions against subscribers.</div>
        </div>

        <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.platform') }}">Back to Subscribers</a>
    </div>

    @if(!empty($tableMissing))
        <div class="alert alert-warning">
            The audit log table <code>sharpfleet_audit_logs</code> does not exist yet.
            Create it in phpMyAdmin, then refresh this page.
        </div>
    @endif

    <div class="card sl-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sharpfleet.audit.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-2">
                    <label class="form-label">Organisation ID</label>
                    <input type="text" name="organisation_id" value="{{ $filters['organisation_id'] ?? '' }}" class="form-control" placeholder="e.g. 123">
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label">Actor type</label>
                    <select name="actor_type" class="form-select">
                        <option value="">All</option>
                        <option value="subscriber" @selected(($filters['actor_type'] ?? '') === 'subscriber')>Subscriber</option>
                        <option value="platform_admin" @selected(($filters['actor_type'] ?? '') === 'platform_admin')>Platform Admin</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label">Actor email</label>
                    <input type="text" name="actor_email" value="{{ $filters['actor_email'] ?? '' }}" class="form-control" placeholder="contains…">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label">Action</label>
                    <input type="text" name="action" value="{{ $filters['action'] ?? '' }}" class="form-control" placeholder="contains…">
                </div>

                <div class="col-6 col-md-1">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>

                <div class="col-6 col-md-1">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="action, actor, path, context…">
                </div>

                <div class="col-12 col-md-6 d-flex gap-2 justify-content-end">
                    <button class="btn btn-primary" type="submit">Apply</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.audit.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card sl-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>When (Brisbane)</th>
                            <th class="text-end">Org</th>
                            <th>Actor</th>
                            <th>Action</th>
                            <th>Request</th>
                            <th class="text-end">Status</th>
                            <th>Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="white-space:nowrap;">
                                    @if(!empty($log->created_at))
                                        {{ \Carbon\Carbon::parse($log->created_at, 'UTC')->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y H:i:s') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">{{ (int)($log->organisation_id ?? 0) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $log->actor_type ?? '—' }}</div>
                                    <div class="text-muted small">{{ $log->actor_email ?? '—' }}</div>
                                </td>
                                <td>
                                    @php($actionRaw = (string)($log->action ?? ''))
                                    @php($actionMap = [
                                        'sharpfleet.organisation.update' => 'Platform Admin Updated Subscriber',
                                        'sharpfleet.organisation.edit.view' => 'Platform Admin Viewed Subscriber Edit',
                                        'sharpfleet.organisation.view' => 'Platform Admin Viewed Subscriber',
                                        'sharpfleet.organisation.user.update' => 'Platform Admin Updated User Trial',
                                        'sharpfleet.organisation.user.edit.view' => 'Platform Admin Viewed User Edit',
                                        'Billing: Vehicle Added' => 'Subscriber Added Vehicle (Billing)',
                                        'Billing: Vehicle Archived' => 'Subscriber Archived Vehicle (Billing)',
                                        'Billing: Stripe Subscription Updated' => 'Stripe Subscription Updated',
                                        'Billing: Stripe Subscription Update Failed' => 'Stripe Subscription Update Failed',
                                    ])
                                    <div class="fw-semibold">{{ $actionMap[$actionRaw] ?? ($log->action ?? '—') }}</div>
                                    @php($ctxJson = (string)($log->context_json ?? ''))
                                    @php($ctxArr = $ctxJson !== '' ? (json_decode($ctxJson, true) ?? []) : [])
                                    @if(!empty($ctxArr['summary']))
                                        <div class="text-muted small">{{ $ctxArr['summary'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small">{{ strtoupper($log->method ?? '') }} {{ $log->path ?? '' }}</div>
                                </td>
                                <td class="text-end">
                                    {{ $log->status_code ?? '—' }}
                                </td>
                                <td style="max-width: 420px;">
                                    @php($ctx = (string)($log->context_json ?? ''))
                                    @if($ctx !== '')
                                        <details>
                                            <summary class="text-muted small">View</summary>
                                            <pre class="small mb-0" style="white-space: pre-wrap;">{{ $ctx }}</pre>
                                        </details>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs instanceof \Illuminate\Contracts\Pagination\Paginator || $logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-white border-0 d-flex justify-content-end">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
