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

        <div class="mb-3">
            <div class="info-item">
                <div class="info-label">Role</div>
                <div class="info-value">{{ $user->role }}</div>
            </div>
        </div>

        <form method="POST" action="{{ url('/app/sharpfleet/admin/users/'.$user->id) }}">
            @csrf

            <input type="hidden" name="is_driver" value="0">

            <label class="checkbox-label mb-2">
                <input type="checkbox" name="is_driver" value="1" {{ (int)($user->is_driver ?? 0) === 1 ? 'checked' : '' }}>
                <strong>Enable Driver View access</strong>
            </label>

            <p class="text-muted small mb-3">
                If enabled, this user can open /app/sharpfleet/driver. This is useful when an admin also drives.
            </p>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ url('/app/sharpfleet/admin/users') }}" class="btn btn-secondary">Cancel</a>
            </div>

            @php
                $branchesEnabled = (bool) ($branchesEnabled ?? false);
                $branches = $branches ?? collect();
                $selectedBranchIds = is_array($selectedBranchIds ?? null) ? $selectedBranchIds : [];
                $selectedBranchMap = [];
                foreach ($selectedBranchIds as $id) { $selectedBranchMap[(int) $id] = true; }
            @endphp

            @if($branchesEnabled)
                <hr class="my-4">

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
        </form>

        @if(($user->role ?? '') === 'driver')
            <hr class="my-4">

            <form method="POST" action="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/delete') }}"
                  onsubmit="return confirm('Archive this driver? They will no longer be able to log in, book vehicles, or log trips.');">
                @csrf

                <button type="submit" class="btn btn-danger">
                    Archive driver
                </button>
            </form>
        @endif
    </div>
</div>

@endsection
