@extends('layouts.sharpfleet') 

@section('title', 'Vehicles')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Vehicles</h1>
                <p class="page-description">Manage vehicles for your organisation.</p>
            </div>
            <a href="{{ url('/app/sharpfleet/admin/vehicles/create') }}" class="btn btn-secondary">+ Add Vehicle</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ url('/app/sharpfleet/admin/vehicles') }}" class="mb-3" id="sf-vehicles-filter">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-end">
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted small" for="sf-vehicle-search" style="margin-bottom:0;">Search</label>
                        <div class="position-relative" style="position:relative; width: 220px;">
                            <input
                                type="text"
                                class="form-control"
                                id="sf-vehicle-search"
                                name="search"
                                value="{{ $search ?? '' }}"
                                placeholder="Name, rego, make"
                                autocomplete="off"
                                style="padding-right:28px;">
                            <button type="button"
                                    id="sf-vehicle-search-clear"
                                    class="btn btn-sm btn-secondary"
                                    aria-label="Clear search"
                                    title="Clear"
                                    style="position:absolute; right:6px; top:50%; transform:translateY(-50%); padding:2px 6px; line-height:1; display:none;">
                                &times;
                            </button>
                            <div id="sf-vehicle-search-results"
                                 class="list-group"
                                 style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:240px; overflow:auto; background:#fff; border:1px solid #dee2e6; border-radius:8px; box-shadow:0 10px 24px rgba(16,24,40,0.12);"></div>
                        </div>
                    </div>
                </div>
            </form>
            @if($vehicles->count() === 0)
                <p class="text-muted fst-italic">No vehicles found.</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Registration</th>
                                <th>Status</th>
                                <th>Active Trip</th>
                                <th>Type</th>
                                <th>Class</th>
                                <th>Make/Model</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $v)
                                <tr>
                                    <td><a href="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/details') }}">{{ $v->name }}</a></td>
                                    <td>{{ $v->registration_number }}</td>
                                    <td>
                                        @php
                                            $isInService = isset($v->is_in_service) ? (int) $v->is_in_service : 1;
                                            $reason = $v->out_of_service_reason ?? null;
                                            $note = $v->out_of_service_note ?? null;
                                        @endphp

                                        @if($isInService === 0)
                                            <div class="fw-bold text-error">Out of service</div>
                                            <div class="text-muted">
                                                {{ $reason ?: '—' }}
                                            </div>
                                            @if($note)
                                                <div class="text-muted">{{ $note }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">In service</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($activeTripVehicleIds[$v->id]))
                                            <div class="fw-bold">In trip</div>
                                            <div class="text-muted">
                                                {{ $activeTripsByVehicle[$v->id]['driver_name'] ?? '—' }}
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($v->vehicle_type) }}</td>
                                    <td>{{ $v->vehicle_class ?? '—' }}</td>
                                    <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '—' }}</td>
                                    <td>
                                        <div class="btn-group-sm">
                                            <a href="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/edit') }}" class="btn-sf-navy btn-sm">Edit</a>
                                            @if(!empty($isSubscribed))
                                                <a href="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/archive/confirm') }}" class="btn btn-danger btn-sm">Archive</a>
                                            @else
                                                <form method="POST"
                                                    action="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/archive') }}"
                                                    class="d-inline"
                                                    data-sf-confirm
                                                    data-sf-confirm-title="Archive vehicle"
                                                    data-sf-confirm-message="Archive this vehicle? Drivers will no longer be able to select it."
                                                    data-sf-confirm-text="Archive"
                                                    data-sf-confirm-variant="danger">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">Archive</button>
                                                </form>
                                            @endif
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
    .sf-vehicle-search-item {
        border: 0;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 0;
        background: #fff;
        padding: 10px 12px;
    }
    .sf-vehicle-search-item:last-child {
        border-bottom: 0;
    }
    .sf-vehicle-search-item:focus {
        box-shadow: none;
    }
    #sf-vehicle-search-results .list-group-item-action:hover {
        background: #f4f7fb;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const searchItemClass = 'sf-vehicle-search-item';
        const filterForm = document.getElementById('sf-vehicles-filter');
        const searchInput = document.getElementById('sf-vehicle-search');
        const searchResults = document.getElementById('sf-vehicle-search-results');
        const searchClear = document.getElementById('sf-vehicle-search-clear');
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
                    const registration = item.registration || '';
                    const make = item.make || '';
                    const model = item.model || '';

                    const title = document.createElement('div');
                    title.className = 'fw-semibold';
                    title.textContent = name !== '' ? name : registration;

                    const meta = document.createElement('div');
                    meta.className = 'text-muted small';
                    const parts = [];
                    if (registration) parts.push(registration);
                    const makeModel = [make, model].filter(Boolean).join(' ');
                    if (makeModel) parts.push(makeModel);
                    meta.textContent = parts.join(' - ');

                    button.appendChild(title);
                    if (meta.textContent !== '') {
                        button.appendChild(meta);
                    }

                    button.addEventListener('click', function () {
                        if (!searchInput || !filterForm) return;
                        searchInput.value = registration !== '' ? registration : name;
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

            const url = `/app/sharpfleet/admin/vehicles/search?query=${encodeURIComponent(query)}`;

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
