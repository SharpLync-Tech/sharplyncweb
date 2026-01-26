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
            <form method="GET" action="{{ url('/app/sharpfleet/admin/customers') }}" class="mb-3">
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
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            value="{{ $searchQuery ?? '' }}"
                            placeholder="Search customers"
                        >
                    </div>

                    <div>
                        <button type="submit" class="btn btn-secondary">Apply</button>
                        <a href="{{ url('/app/sharpfleet/admin/customers') }}" class="btn btn-light">Reset</a>
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

@endsection
