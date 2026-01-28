@extends('layouts.sharpfleet')

@section('title', 'Customers')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Customers</h1>
                <p class="page-description">Manage your customer/client list for driver trip logging.</p>
            </div>
            <div>
                <a href="{{ url('/app/sharpfleet/admin/customers/create') }}" class="btn btn-primary">+ Add Customers</a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('customers'))
        <div class="alert alert-error">
            {{ $errors->first('customers') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Customer List</h2>
        </div>
        <div class="card-body">
            <form
                method="GET"
                action="{{ url('/app/sharpfleet/admin/customers') }}"
                class="mb-3"
                id="sf-customers-filter"
            >
                <div class="grid grid-3 align-end">
                    @if(($isCompanyAdmin ?? false) && ($branchesEnabled ?? false) && ($hasCustomerBranch ?? false) && ($branches->count() > 1))
                        <div>
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-control" id="sf-customers-branch">
                                <option value="">All branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (int) ($selectedBranchId ?? 0) === (int) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="form-label">Search</label>
                        <div class="sf-search position-relative" style="position:relative; width: 220px;">
                            <input
                                type="text"
                                name="q"
                                class="form-control"
                                placeholder="Search customers"
                                id="sf-customer-search"
                                value="{{ $searchQuery ?? '' }}"
                                autocomplete="off"
                                style="padding-right:28px;"
                            >
                            <button
                                type="button"
                                class="btn btn-sm btn-secondary"
                                id="sf-customer-search-clear"
                                aria-label="Clear search"
                                title="Clear"
                                style="position:absolute; right:6px; top:50%; transform:translateY(-50%); padding:2px 6px; line-height:1; display:none;"
                            >
                                &times;
                            </button>
                            <div id="sf-customer-search-results"
                                 class="list-group"
                                 style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:240px; overflow:auto; background:#fff; border:1px solid #dee2e6; border-radius:8px; box-shadow:0 10px 24px rgba(16,24,40,0.12);"></div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn-sf-navy">Apply</button>
                        <a href="{{ url('/app/sharpfleet/admin/customers') }}" class="btn-sf-navy">Reset</a>
                    </div>
                </div>
            </form>

            @if(!$customersTableExists)
                <p class="text-muted fst-italic">Customer management is unavailable until the database table is created.</p>
            @elseif($customers->count() === 0)
                <p class="text-muted fst-italic">No customers yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th style="width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $c)
                                <tr>
                                    <td class="fw-bold">{{ $c->name }}</td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a class="btn-sf-navy btn-sm" href="{{ url('/app/sharpfleet/admin/customers/' . $c->id . '/edit') }}">Edit</a>

                                            <form method="POST"
                                                  action="{{ url('/app/sharpfleet/admin/customers/' . $c->id . '/archive') }}"
                                                  data-sf-confirm
                                                  data-sf-confirm-title="Archive customer"
                                                  data-sf-confirm-message="Archive this customer? They will be hidden from the list."
                                                  data-sf-confirm-text="Archive"
                                                  data-sf-confirm-variant="danger">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Archive</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .sf-customer-search-item {
        border: 0;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 0;
        background: #fff;
        padding: 10px 12px;
    }
    .sf-customer-search-item:last-child {
        border-bottom: 0;
    }
    .sf-customer-search-item:focus {
        box-shadow: none;
    }
    #sf-customer-search-results .list-group-item-action:hover {
        background: #f4f7fb;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const searchItemClass = 'sf-customer-search-item';
        const filterForm = document.getElementById('sf-customers-filter');
        const branchSelect = document.getElementById('sf-customers-branch');
        const searchInput = document.getElementById('sf-customer-search');
        const searchResults = document.getElementById('sf-customer-search-results');
        const searchClear = document.getElementById('sf-customer-search-clear');
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
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `list-group-item list-group-item-action text-start ${searchItemClass}`;

                    const name = item.name || '';
                    const nameEl = document.createElement('div');
                    nameEl.className = 'fw-semibold';
                    nameEl.textContent = name;
                    button.appendChild(nameEl);

                    button.addEventListener('click', function () {
                        if (!searchInput || !filterForm) return;
                        searchInput.value = name;
                        clearSearchResults();
                        filterForm.submit();
                    });

                    frag.appendChild(button);
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
            if (canAbortSearch && searchAbort) {
                searchAbort.abort();
            }

            const controller = canAbortSearch ? new AbortController() : null;
            if (controller) {
                searchAbort = controller;
            }

            const branchId = branchSelect ? branchSelect.value : '';
            const url = `/app/sharpfleet/admin/customers/search?query=${encodeURIComponent(query)}&branch_id=${encodeURIComponent(branchId)}`;

            const showError = () => showSearchStatus('Search unavailable');

            if (canFetch) {
                showSearchStatus('Searching...');
                const fetchOptions = controller ? { signal: controller.signal } : {};
                fetch(url, fetchOptions)
                    .then(res => (res.ok ? res.json() : []))
                    .then(items => renderSearchResults(items, query))
                    .catch(err => {
                        if (err && err.name === 'AbortError') return;
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
                        renderSearchResults(items, query);
                    } catch (e) {
                        showError();
                    }
                } else {
                    showError();
                }
            };
            xhr.onerror = showError;
            xhr.send();
        }

        if (searchInput) {
            if (searchClear) {
                searchClear.style.display = searchInput.value.trim() ? 'block' : 'none';
            }

            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim();
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
    })();
</script>
@endpush

@endsection


