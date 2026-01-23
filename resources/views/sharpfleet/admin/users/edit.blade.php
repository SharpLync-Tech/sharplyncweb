@extends('layouts.sharpfleet')

@section('title', 'Edit User')

@section('sharpfleet-content')

<div class="max-w-700 mx-auto mt-4">
    <div class="page-header">
        <h1 class="page-title">Edit User</h1>
        <p class="page-description">Toggle whether this user can access the Driver View.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <div class="card">
        <div class="mb-3">
            <div class="info-item">
                <div class="info-label">Name</div>
                <div class="info-value">{{ trim($user->first_name.' '.$user->last_name) }}</div>
            </div>
        </div>

        <div class="mb-3">
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $user->email }}</div>
            </div>
        </div>

        @php
            $sfActor = session('sharpfleet.user');
            $sfActorCanSetGroups = $sfActor ? \App\Support\SharpFleet\Roles::canSetUserGroups($sfActor) : false;
            $sfActorIsCompanyAdmin = $sfActor ? (\App\Support\SharpFleet\Roles::normalize($sfActor['role'] ?? null) === \App\Support\SharpFleet\Roles::COMPANY_ADMIN) : false;

            $sfTargetRole = \App\Support\SharpFleet\Roles::normalize($user->role ?? null);
            $sfRoleOptions = $sfActorIsCompanyAdmin
                ? [
                    \App\Support\SharpFleet\Roles::COMPANY_ADMIN => 'Company admin',
                    \App\Support\SharpFleet\Roles::BRANCH_ADMIN => 'Branch admin',
                    \App\Support\SharpFleet\Roles::BOOKING_ADMIN => 'Booking admin',
                    \App\Support\SharpFleet\Roles::DRIVER => 'Driver',
                ]
                : [
                    \App\Support\SharpFleet\Roles::BRANCH_ADMIN => 'Branch admin',
                    \App\Support\SharpFleet\Roles::BOOKING_ADMIN => 'Booking admin',
                    \App\Support\SharpFleet\Roles::DRIVER => 'Driver',
                ];
        @endphp

        <div class="mb-3">
            <div class="info-item">
                <div class="info-label">User group</div>
                <div class="info-value">
                    @if($sfActorCanSetGroups)
                        <div style="max-width: 360px;">
                            <select class="form-control" name="role" form="sfUserEditForm" {{ ($sfTargetRole === \App\Support\SharpFleet\Roles::COMPANY_ADMIN && !$sfActorIsCompanyAdmin) ? 'disabled' : '' }}>
                                @foreach($sfRoleOptions as $value => $label)
                                    <option value="{{ $value }}" {{ (old('role', $sfTargetRole) === $value) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if($sfTargetRole === \App\Support\SharpFleet\Roles::COMPANY_ADMIN && !$sfActorIsCompanyAdmin)
                                <div class="text-muted small mt-1">Only company admins can change a company admin's group.</div>
                            @endif
                            @error('role') <div class="text-error mt-1">{{ $message }}</div> @enderror
                        </div>
                    @else
                        {{ $sfTargetRole }}
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4"></div>

        <form method="POST" action="{{ url('/app/sharpfleet/admin/users/'.$user->id) }}" id="sfUserEditForm">
            @csrf

            <input type="hidden" name="is_driver" value="0">

            <label class="checkbox-label mb-2">
                <input type="checkbox" name="is_driver" value="1" {{ (int)($user->is_driver ?? 0) === 1 ? 'checked' : '' }}>
                <strong>Enable Driver View access</strong>
            </label>

            <p class="text-muted small mb-3">
                If enabled, this user can open /app/sharpfleet/driver. This is useful when an admin also drives.
            </p>

            @php
                $branchesEnabled = (bool) ($branchesEnabled ?? false);
                $branches = $branches ?? collect();
                $selectedBranchIds = is_array($selectedBranchIds ?? null) ? $selectedBranchIds : [];
                $selectedBranchMap = [];
                foreach ($selectedBranchIds as $id) { $selectedBranchMap[(int) $id] = true; }
            @endphp

            @if($branchesEnabled)
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
                            <input type="checkbox" name="branch_ids[]" value="{{ $bid }}" {{ $checked ? 'checked' : '' }}>
                            <strong>{{ (string) ($b->name ?? '') }}</strong>
                            <span class="text-muted">({{ (string) ($b->timezone ?? '') }})</span>
                        </label>
                    @endforeach
                @endif

                @error('branch_ids') <div class="text-error mb-2">{{ $message }}</div> @enderror
                @error('branch_ids.*') <div class="text-error mb-2">{{ $message }}</div> @enderror
            @endif

            <div class="mt-4"></div>
        </form>

        @if((int) ($user->is_driver ?? 0) === 1 && empty($user->archived_at))
            <div class="mt-4"></div>

            <div>
                <h3 class="section-title">Mobile sessions</h3>
                <p class="text-muted small mb-3">
                    Revokes all active mobile device tokens for this driver. Admin and desktop sessions are unaffected.
                </p>

                <form method="POST" action="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/revoke-mobile-tokens') }}" onsubmit="return confirm('Revoke all mobile sessions for this driver?');">
                    @csrf
                    <button type="submit" class="btn btn-warning">Revoke all mobile sessions</button>
                </form>
            </div>
        @endif

        @if(($user->role ?? '') === 'driver')
            <div class="mt-4"></div>

            <div>
                <h3 class="section-title">Archive</h3>
                <p class="text-muted small mb-3">
                    Archived drivers cannot log in, book vehicles, or log trips. Historical trip data remains.
                </p>

                <button type="button" class="btn btn-danger" id="sfArchiveDriverBtn">
                    Archive driver
                </button>
            </div>
        @endif
    </div>

    <div class="btn-group mt-3">
        <button type="submit" class="btn btn-primary" form="sfUserEditForm">Save</button>
        <a href="{{ url('/app/sharpfleet/admin/users') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>

@if(($user->role ?? '') === 'driver')
    <div id="sfArchiveDriverModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
        <div class="card" style="max-width:520px; margin:10vh auto;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h3 class="mb-1">Archive driver</h3>
                        <p class="text-muted mb-0">
                            Archive this driver? They will no longer be able to log in, book vehicles, or log trips.
                        </p>
                    </div>
                    <button type="button"
                            class="btn btn-secondary btn-sm"
                            id="sfArchiveDriverClose"
                            aria-label="Close"
                            title="Close"
                            style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; padding:0; font-size:22px; line-height:1;">
                        &times;
                    </button>
                </div>

                <div class="mt-3"></div>

                <form method="POST" action="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/delete') }}" id="sfArchiveDriverForm">
                    @csrf

                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" id="sfArchiveDriverCancel">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="sfArchiveDriverConfirm">Archive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('sfArchiveDriverBtn');
            const modal = document.getElementById('sfArchiveDriverModal');
            const closeBtn = document.getElementById('sfArchiveDriverClose');
            const cancelBtn = document.getElementById('sfArchiveDriverCancel');

            function closeModal() {
                if (!modal) return;
                modal.style.display = 'none';
            }

            function openModal() {
                if (!modal) return;
                modal.style.display = 'block';
            }

            if (btn) btn.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });
            }
        })();
    </script>
@endif

@endsection
