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
                x-data="customerSearch('{{ $searchQuery ?? '' }}')"
                x-init="init()"
                x-ref="form"
                method="GET"
                action="{{ url('/app/sharpfleet/admin/customers') }}"
                class="mb-3"
            >
                <div class="grid grid-3 align-end">
                    @if(($isCompanyAdmin ?? false) && ($branchesEnabled ?? false) && ($hasCustomerBranch ?? false) && ($branches->count() > 1))
                        <div>
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-control">
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
                        <div class="sf-search">
                            <input
                                type="text"
                                name="q"
                                class="form-control sf-report-input"
                                placeholder="Search customers"
                                x-model="q"
                                x-on:input.debounce.350ms="submit()"
                                autocomplete="off"
                            >
                            <button
                                type="button"
                                class="sf-search__clear"
                                x-show="q"
                                x-on:click="clear()"
                                aria-label="Clear search"
                            >
                                ×
                            </button>
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
    .sf-report-input.form-control {
        border-radius: 12px;
        border: 1px solid rgba(44, 191, 174, 0.35);
        padding: 10px 14px;
        background-color: #f8fcfb;
        font-weight: 600;
        font-size: 0.95rem;
        color: #0A2A4D;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.85),
            0 1px 2px rgba(10, 42, 77, 0.05);
        transition:
            border-color 150ms ease,
            box-shadow 150ms ease,
            background-color 150ms ease;
    }

    .sf-report-input.form-control:hover {
        background-color: #ffffff;
        border-color: #2CBFAE;
    }

    .sf-report-input.form-control:focus {
        outline: none;
        background-color: #ffffff;
        border-color: #2CBFAE;
        box-shadow:
            0 0 0 3px rgba(44, 191, 174, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .sf-search {
        position: relative;
    }

    .sf-search__clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #6b7a90;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function customerSearch(initialQuery) {
        return {
            q: initialQuery || '',
            init() {
                this.q = initialQuery || '';
            },
            submit() {
                if (this.$refs.form) this.$refs.form.submit();
            },
            clear() {
                this.q = '';
                this.submit();
            }
        };
    }
</script>
@endpush

@endsection
