@extends('layouts.sharpfleet')

@section('title', 'Users')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <p class="page-description">
            Manage driver access for users in your organisation. Enabling driver access lets an admin use the Driver View.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a class="btn btn-primary" href="/app/sharpfleet/admin/users/invite">Invite Driver</a>
            <a class="btn btn-secondary" href="/app/sharpfleet/admin/users/add">Add Driver</a>
            <a class="btn btn-secondary" href="/app/sharpfleet/admin/users/import">Import CSV</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit" form="sf-users-invites" formaction="/app/sharpfleet/admin/users/send-invites">
                        Send invites (selected)
                    </button>
                </div>

                <div>
                    <form method="GET" action="/app/sharpfleet/admin/users" id="sf-users-filter" class="d-flex gap-2 align-items-center">
                        <label class="text-muted small" for="status" style="margin-bottom:0;">Show</label>
                        <select class="form-control" id="status" name="status" style="max-width: 220px;">
                            <option value="active" {{ (($status ?? 'active') === 'active') ? 'selected' : '' }}>Active users</option>
                            <option value="archived" {{ (($status ?? 'active') === 'archived') ? 'selected' : '' }}>Archived users</option>
                            <option value="all" {{ (($status ?? 'active') === 'all') ? 'selected' : '' }}>All users</option>
                        </select>
                    </form>
                </div>
            </div>

            <form method="POST" id="sf-users-invites">
                @csrf

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="sf-select-all-invites">
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Driver access</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php
                                    $isPendingDriver = (($user->role ?? '') === 'driver' && ($user->account_status ?? '') === 'pending');
                                    $hasInviteLink = !empty($user->activation_expires_at);
                                    $isArchived = !empty($user->archived_at);
                                    $showReEnable = (($status ?? 'active') === 'archived') || ((($status ?? 'active') === 'all') && $isArchived);
                                @endphp
                                <tr class="{{ $isArchived ? 'text-muted' : '' }}">
                                    <td>
                                        @if($isPendingDriver && !$isArchived)
                                            <input
                                                type="checkbox"
                                                class="sf-invite-checkbox"
                                                name="user_ids[]"
                                                value="{{ $user->id }}">
                                        @endif
                                    </td>
                                    <td>{{ trim($user->first_name.' '.$user->last_name) }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role }}</td>
                                    <td>
                                        @if($isArchived)
                                            <span class="badge text-bg-secondary border">Archived</span>
                                        @elseif(($user->account_status ?? '') === 'pending')
                                            @if($hasInviteLink)
                                                <span class="text-muted">Pending invite</span>
                                            @else
                                                <span class="text-muted">Pending (not invited)</span>
                                            @endif
                                        @else
                                            <span class="text-primary fw-bold">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if((int)($user->is_driver ?? 0) === 1)
                                            <span class="text-primary fw-bold">Enabled</span>
                                        @else
                                            <span class="text-muted">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="d-flex gap-2 justify-content-end">
                                            @if($isPendingDriver && $hasInviteLink && !$isArchived)
                                                <button
                                                    class="btn btn-secondary btn-sm"
                                                    type="submit"
                                                    formaction="/app/sharpfleet/admin/users/{{ $user->id }}/resend-invite">
                                                    Resend invite
                                                </button>
                                            @endif

                                            @if($showReEnable)
                                                <button
                                                    type="button"
                                                    class="btn btn-secondary btn-sm"
                                                    data-unarchive-user-id="{{ $user->id }}"
                                                    data-unarchive-user-name="{{ addslashes(trim($user->first_name.' '.$user->last_name)) }}">
                                                    Re-enable user
                                                </button>
                                            @else
                                                <a class="btn btn-secondary btn-sm" href="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/edit') }}">
                                                    Edit
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="sfUnarchiveModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
    <div class="card" style="max-width:520px; margin:10vh auto;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h3 class="mb-1">Re-enable user</h3>
                    <p class="text-muted mb-0" id="sfUnarchiveMessage">
                        Re-enable this user? They will regain access to log in, book vehicles, and log trips.
                    </p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="sfUnarchiveClose">Close</button>
            </div>

            <hr class="my-3">

            <form method="POST" id="sfUnarchiveForm">
                @csrf
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" id="sfUnarchiveCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="sfUnarchiveConfirm">Re-enable user</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const selectAll = document.getElementById('sf-select-all-invites');
        if (!selectAll) return;

        function getBoxes() {
            return Array.from(document.querySelectorAll('.sf-invite-checkbox'));
        }

        selectAll.addEventListener('change', function () {
            const boxes = getBoxes();
            boxes.forEach(cb => { cb.checked = selectAll.checked; });
        });
    })();

    (function () {
        const filterForm = document.getElementById('sf-users-filter');
        const statusSelect = document.getElementById('status');
        if (filterForm && statusSelect) {
            statusSelect.addEventListener('change', function () {
                filterForm.submit();
            });
        }

        const modal = document.getElementById('sfUnarchiveModal');
        const closeBtn = document.getElementById('sfUnarchiveClose');
        const cancelBtn = document.getElementById('sfUnarchiveCancel');
        const form = document.getElementById('sfUnarchiveForm');

        function closeModal() {
            if (!modal) return;
            modal.style.display = 'none';
            if (form) form.removeAttribute('action');
        }

        function openModal(actionUrl) {
            if (!modal || !form) return;
            form.setAttribute('action', actionUrl);
            modal.style.display = 'block';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
        }

        document.querySelectorAll('[data-unarchive-user-id]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const userId = btn.getAttribute('data-unarchive-user-id');
                if (!userId) return;
                openModal('/app/sharpfleet/admin/users/' + encodeURIComponent(userId) + '/unarchive');
            });
        });
    })();
</script>

@endsection
