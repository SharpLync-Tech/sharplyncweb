@extends('layouts.sharpfleet')

@section('title', 'User Admin')

@section('sharpfleet-content')

<div class="max-w-900 mx-auto mt-4">
    <div class="page-header">
        <h1 class="page-title">User Admin</h1>
        <p class="page-description">Manage user groups and branch access for your organisation.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="/app/sharpfleet/admin/user-rights" class="d-flex flex-wrap gap-2 align-items-center">
                <label class="text-muted small" for="user_id" style="margin-bottom:0;">Select user</label>
                <select class="form-control" id="user_id" name="user_id" style="max-width: 360px;">
                    <option value="">Choose a user...</option>
                    @foreach($users as $u)
                        @php
                            $label = trim(($u->first_name ?? '').' '.($u->last_name ?? ''));
                            $label = $label !== '' ? $label : ($u->email ?? 'User #' . $u->id);
                        @endphp
                        <option value="{{ $u->id }}" {{ ($selectedUser && (int) $selectedUser->id === (int) $u->id) ? 'selected' : '' }}>
                            {{ $label }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary">Load</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h3 class="section-title">Role summary</h3>
            <p class="text-muted small mb-2">Quick guide to what each role can do.</p>
            <ul class="text-muted small mb-0">
                <li><strong>Company admin</strong> – Full access across the company and all branches.</li>
                <li><strong>Branch admin</strong> – Full access within assigned branch(es); reports scoped to their branches with no company-wide view.</li>
                <li><strong>Booking admin</strong> – Manage vehicle bookings for assigned branch(es).</li>
                <li><strong>Driver</strong> – Book and drive vehicles within assigned branch(es).</li>
            </ul>
        </div>
    </div>

    @if($selectedUser)
        @php
            $isArchived = !empty($selectedUser->archived_at ?? null);
            $sfTargetRole = \App\Support\SharpFleet\Roles::normalize($selectedUser->role ?? null);
            $sfRoleOptions = [
                \App\Support\SharpFleet\Roles::COMPANY_ADMIN => 'Company admin',
                \App\Support\SharpFleet\Roles::BRANCH_ADMIN => 'Branch admin',
                \App\Support\SharpFleet\Roles::BOOKING_ADMIN => 'Booking admin',
                \App\Support\SharpFleet\Roles::DRIVER => 'Driver',
            ];

            $selectedBranchIds = is_array($selectedBranchIds ?? null) ? $selectedBranchIds : [];
            $selectedBranchMap = [];
            foreach ($selectedBranchIds as $id) { $selectedBranchMap[(int) $id] = true; }
        @endphp

        @if($isArchived)
            <div class="alert alert-error mb-3">
                This user is archived. Re-enable them before changing rights.
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-item">
                        <div class="info-label">Name</div>
                        <div class="info-value">{{ trim($selectedUser->first_name.' '.$selectedUser->last_name) }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $selectedUser->email }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ url('/app/sharpfleet/admin/users/'.$selectedUser->id) }}" id="sfUserRightsForm">
                    @csrf

                    <input type="hidden" name="return_to" value="/app/sharpfleet/admin/user-rights?user_id={{ $selectedUser->id }}">
                    <input type="hidden" name="is_driver" value="0">

                    <div class="mb-3">
                        <div class="info-item">
                            <div class="info-label">User group</div>
                            <div class="info-value">
                                <div style="max-width: 360px;">
                                    <select class="form-control" name="role" {{ $isArchived ? 'disabled' : '' }}>
                                        @foreach($sfRoleOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (old('role', $sfTargetRole) === $value) ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('role') <div class="text-error mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="checkbox-label mb-2">
                        <input type="checkbox" name="is_driver" value="1" {{ (int)($selectedUser->is_driver ?? 0) === 1 ? 'checked' : '' }} {{ $isArchived ? 'disabled' : '' }}>
                        <strong>Enable Driver View access</strong>
                    </label>

                    <p class="text-muted small mb-3">
                        If enabled, this user can open /app/sharpfleet/driver. This is useful when an admin also drives.
                    </p>

                    @if($branchAccessEnabled)
                        <div class="mt-4"></div>

                        <h3 class="section-title">Branch access</h3>
                        <p class="text-muted small mb-2">
                            This controls which branches this user can operate in.
                        </p>

                        @if($branches->count() === 0)
                            <div class="alert alert-error mb-3">No active branches found.</div>
                        @else
                            @foreach($branches as $b)
                                @php
                                    $bid = (int) ($b->id ?? 0);
                                    $checked = old('branch_ids') !== null
                                        ? in_array((string) $bid, (array) old('branch_ids', []), true) || in_array($bid, (array) old('branch_ids', []), true)
                                        : isset($selectedBranchMap[$bid]);
                                @endphp
                                <label class="checkbox-label mb-1">
                                    <input type="checkbox" name="branch_ids[]" value="{{ $bid }}" {{ $checked ? 'checked' : '' }} {{ $isArchived ? 'disabled' : '' }}>
                                    <strong>{{ (string) ($b->name ?? '') }}</strong>
                                    <span class="text-muted">({{ (string) ($b->timezone ?? '') }})</span>
                                </label>
                            @endforeach
                        @endif

                        @error('branch_ids') <div class="text-error mb-2">{{ $message }}</div> @enderror
                        @error('branch_ids.*') <div class="text-error mb-2">{{ $message }}</div> @enderror
                    @else
                        <div class="alert alert-info mb-3">
                            Branch access is not enabled for this account.
                        </div>
                    @endif

                    <div class="mt-4"></div>
                </form>

                <div class="btn-group mt-3">
                    <button type="submit" class="btn btn-primary" form="sfUserRightsForm" {{ $isArchived ? 'disabled' : '' }}>Save</button>
                    <a href="{{ url('/app/sharpfleet/admin/users') }}" class="btn btn-secondary">Back to Users</a>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
