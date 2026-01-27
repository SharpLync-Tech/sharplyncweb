@extends('layouts.sharpfleet')

@section('title', 'Users')

@section('sharpfleet-content')

<div class="container">
    @php
        $sfActor = session('sharpfleet.user');
        $sfCanManageUsers = $sfActor ? \App\Support\SharpFleet\Roles::canManageUsers($sfActor) : false;
        $sfIsCompanyAdmin = $sfActor ? \App\Support\SharpFleet\Roles::isCompanyAdmin($sfActor) : false;
    @endphp

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

    @if($sfCanManageUsers)
        <div class="mb-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a class="btn btn-secondary" href="/app/sharpfleet/admin/users/invite">Invite Driver</a>
                <a class="btn btn-secondary" href="/app/sharpfleet/admin/users/add">Add Driver</a>
                @if($sfCanManageUsers)
                    <button class="btn btn-primary" type="submit" form="sf-users-invites" formaction="/app/sharpfleet/admin/users/send-invites">
                        Send invites (selected)
                    </button>
                @endif
                <a class="btn btn-secondary" href="/app/sharpfleet/admin/users/import">Import CSV</a>
                @if($sfIsCompanyAdmin)
                    <a class="btn btn-secondary" href="/app/sharpfleet/admin/user-rights">User Admin</a>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 mb-3">
                <div>
                    <form method="GET" action="/app/sharpfleet/admin/users" id="sf-users-filter" class="d-flex flex-nowrap gap-2 align-items-center">
                        <label class="text-muted small" for="status" style="margin-bottom:0;">Show</label>
                        <select class="form-control" id="status" name="status" style="max-width: 220px;">
                            <option value="active" {{ (($status ?? 'active') === 'active') ? 'selected' : '' }}>Active users</option>
                            <option value="archived" {{ (($status ?? 'active') === 'archived') ? 'selected' : '' }}>Archived users</option>
                            <option value="all" {{ (($status ?? 'active') === 'all') ? 'selected' : '' }}>All users</option>
                        </select>
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-muted small" for="sf-user-search" style="margin-bottom:0;">Search</label>
                            <div class="position-relative" style="width: 220px;">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sf-user-search"
                                    name="search"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Name or email"
                                    autocomplete="off"
                                    style="padding-right:28px;">
                                <button type="button"
                                        id="sf-user-search-clear"
                                        class="btn btn-sm btn-secondary"
                                        aria-label="Clear search"
                                        title="Clear"
                                        style="position:absolute; right:6px; top:50%; transform:translateY(-50%); padding:2px 6px; line-height:1; display:none;">
                                    &times;
                                </button>
                                <div id="sf-user-search-results"
                                     class="list-group"
                                     style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:240px; overflow:auto; background:#fff; border:1px solid #dee2e6;"></div>
                            </div>
                        </div>
                    </form>
                    <div id="sf-user-search-debug" class="text-muted small mt-1" style="display:none;"></div>
                    <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                        <label class="text-muted small mb-0">Roles</label>
                        <label class="d-flex gap-1 align-items-center text-muted small mb-0">
                            <input type="checkbox" class="sf-role-filter" value="company_admin" style="accent-color:#1aa3a3;">
                            Company admin
                        </label>
                        <label class="d-flex gap-1 align-items-center text-muted small mb-0">
                            <input type="checkbox" class="sf-role-filter" value="branch_admin" style="accent-color:#1aa3a3;">
                            Branch admin
                        </label>
                        <label class="d-flex gap-1 align-items-center text-muted small mb-0">
                            <input type="checkbox" class="sf-role-filter" value="booking_admin" style="accent-color:#1aa3a3;">
                            Booking admin
                        </label>
                        <label class="d-flex gap-1 align-items-center text-muted small mb-0">
                            <input type="checkbox" class="sf-role-filter" value="driver" style="accent-color:#1aa3a3;">
                            Driver
                        </label>
                    </div>
                </div>
            </div>

            <form method="POST" id="sf-users-invites">
                @csrf

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    @if($sfCanManageUsers)
                                        <input type="checkbox" id="sf-select-all-invites">
                                    @endif
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
                                <tr class="{{ $isArchived ? 'text-muted' : '' }}" data-role="{{ $user->role ?? '' }}">
                                    <td>
                                        @if($sfCanManageUsers && $isPendingDriver && !$isArchived)
                                            <input
                                                type="checkbox"
                                                class="sf-invite-checkbox"
                                                name="user_ids[]"
                                                value="{{ $user->id }}">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/details') }}">
                                            {{ trim($user->first_name.' '.$user->last_name) }}
                                        </a>
                                    </td>
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
                                                <a class="btn-sf-navy btn-sm" href="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/edit') }}">
                                                    Edit
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="sf-users-empty-row">
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
                <button type="button"
                        class="btn btn-secondary btn-sm"
                        id="sfUnarchiveClose"
                        aria-label="Close"
                        title="Close"
                        style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; padding:0; font-size:22px; line-height:1;">
                    &times;
                </button>
            </div>

            <div class="mt-3"></div>

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

        const searchInput = document.getElementById('sf-user-search');
        const searchResults = document.getElementById('sf-user-search-results');
        const searchClear = document.getElementById('sf-user-search-clear');
        const searchDebug = document.getElementById('sf-user-search-debug');
        let searchTimer = null;
        let searchAbort = null;
        const canAbortSearch = typeof AbortController !== 'undefined';
        const canFetch = typeof fetch === 'function';

        function clearSearchResults() {
            if (!searchResults) return;
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
        }

        function positionSearchResults() {
            if (!searchResults || !searchInput) return;
            const rect = searchInput.getBoundingClientRect();
            searchResults.style.position = 'absolute';
            searchResults.style.top = `${rect.bottom + window.scrollY}px`;
            searchResults.style.left = `${rect.left + window.scrollX}px`;
            searchResults.style.width = `${rect.width}px`;
            searchResults.style.zIndex = '2000';
        }

        function ensureSearchPortal() {
            if (!searchResults || !document.body) return;
            if (searchResults.parentElement !== document.body) {
                document.body.appendChild(searchResults);
            }
            positionSearchResults();
        }

        function setDebug(message) {
            if (!searchDebug) return;
            if (!message) {
                searchDebug.textContent = '';
                searchDebug.style.display = 'none';
                return;
            }
            searchDebug.textContent = message;
            searchDebug.style.display = 'block';
        }

        function showSearchStatus(message) {
            if (!searchResults) return;
            ensureSearchPortal();
            searchResults.innerHTML = '';
            const item = document.createElement('div');
            item.className = 'list-group-item text-muted';
            item.textContent = message;
            searchResults.appendChild(item);
            searchResults.style.display = 'block';
            searchResults.style.visibility = 'visible';
            searchResults.style.opacity = '1';
            searchResults.style.pointerEvents = 'auto';
        }

        function renderSearchResults(items, query) {
            if (!searchResults) return;
            ensureSearchPortal();

            const frag = document.createDocumentFragment();
            if (!items || items.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'list-group-item text-muted';
                empty.textContent = query ? `No matches for "${query}"` : 'No matches';
                frag.appendChild(empty);
            } else {
                items.forEach(item => {
                    const link = document.createElement('a');
                    link.className = 'list-group-item list-group-item-action';
                    link.href = `/app/sharpfleet/admin/users/${encodeURIComponent(item.id)}/details`;
                    link.textContent = item.email ? `${item.name} - ${item.email}` : item.name;
                    frag.appendChild(link);
                });
            }

            searchResults.innerHTML = '';
            searchResults.appendChild(frag);
            searchResults.style.display = 'block';
            searchResults.style.visibility = 'visible';
            searchResults.style.opacity = '1';
            searchResults.style.pointerEvents = 'auto';
        }

        function runSearch(query) {
            if (!searchInput || !searchResults) return;
            setDebug(`runSearch("${query}") canFetch=${canFetch} canAbort=${canAbortSearch}`);
            if (canAbortSearch && searchAbort) {
                searchAbort.abort();
            }

            const controller = canAbortSearch ? new AbortController() : null;
            if (controller) {
                searchAbort = controller;
            }

            const status = statusSelect ? statusSelect.value : 'active';
            const url = `/app/sharpfleet/admin/users/search?query=${encodeURIComponent(query)}&status=${encodeURIComponent(status)}`;
            setDebug(`Requesting ${url}`);

            const showError = () => showSearchStatus('Search unavailable');

            if (canFetch) {
                showSearchStatus('Searching...');
                const fetchOptions = controller ? { signal: controller.signal } : {};
                fetch(url, fetchOptions)
                    .then(res => (res.ok ? res.json() : []))
                    .then(items => {
                        setDebug(`Results: ${Array.isArray(items) ? items.length : 'invalid'}`);
                        renderSearchResults(items, query);
                    })
                    .catch(err => {
                        if (err && err.name === 'AbortError') return;
                        setDebug(`Fetch error: ${err && err.message ? err.message : err}`);
                        showError();
                    });
                return;
            }

            showSearchStatus('Searching...');
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const items = JSON.parse(xhr.responseText || '[]');
                        setDebug(`XHR results: ${Array.isArray(items) ? items.length : 'invalid'}`);
                        renderSearchResults(items, query);
                    } catch (e) {
                        setDebug('XHR parse error');
                        showError();
                    }
                } else {
                    setDebug(`XHR status ${xhr.status}`);
                    showError();
                }
            };
            xhr.onerror = function () {
                setDebug('XHR error');
                showError();
            };
            xhr.send();
        }

        if (searchInput) {
            if (searchClear) {
                searchClear.style.display = searchInput.value.trim() ? 'block' : 'none';
            }

            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim();
                setDebug('');
                if (searchClear) {
                    searchClear.style.display = query ? 'block' : 'none';
                }
                if (searchTimer) {
                    clearTimeout(searchTimer);
                }
                if (query.length === 0) {
                    clearSearchResults();
                    return;
                }
                searchTimer = setTimeout(() => runSearch(query), 200);
            });

            searchInput.addEventListener('focus', function () {
                const query = searchInput.value.trim();
                if (query.length >= 1) {
                    runSearch(query);
                }
            });
        }

        window.addEventListener('resize', function () {
            if (searchResults && searchResults.style.display === 'block') {
                positionSearchResults();
            }
        });

        window.addEventListener('scroll', function () {
            if (searchResults && searchResults.style.display === 'block') {
                positionSearchResults();
            }
        }, true);

        if (searchClear && searchInput) {
            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                clearSearchResults();
                searchClear.style.display = 'none';
                if (filterForm) {
                    filterForm.submit();
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (!searchResults || !searchInput) return;
            if (searchResults.contains(event.target) || searchInput.contains(event.target)) return;
            clearSearchResults();
        });

        const roleFilters = Array.from(document.querySelectorAll('.sf-role-filter'));
        const userRows = Array.from(document.querySelectorAll('tbody tr[data-role]'));
        const emptyRow = document.getElementById('sf-users-empty-row');

        function applyRoleFilters() {
            if (roleFilters.length === 0) return;
            const activeRoles = roleFilters
                .filter(cb => cb.checked)
                .map(cb => (cb.value || '').toLowerCase());

            let visibleCount = 0;
            userRows.forEach(row => {
                const role = (row.getAttribute('data-role') || '').toLowerCase();
                const show = activeRoles.length === 0 || activeRoles.includes(role);
                row.style.display = show ? '' : 'none';
                if (show) {
                    visibleCount += 1;
                }
            });

            if (emptyRow) {
                if (userRows.length === 0) {
                    emptyRow.style.display = '';
                } else {
                    emptyRow.style.display = visibleCount === 0 ? '' : 'none';
                }
            }
        }

        roleFilters.forEach(cb => {
            cb.addEventListener('change', applyRoleFilters);
        });

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
