@extends('layouts.sharpfleet')

@section('title', 'Add Customers')

@section('sharpfleet-content')

@php
    /**
     * Prefill logic:
     * 1) old('name') after validation error
     * 2) ?name= from Trips "Convert to customer"
     */
    $prefillName = old('name', request()->query('name', ''));
    $branches = $branches ?? collect();
    $branchesEnabled = $branchesEnabled ?? false;
    $hasCustomerBranch = $hasCustomerBranch ?? false;
    $singleBranch = $branches->count() === 1 ? $branches->first() : null;
@endphp

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Add Customers</h1>
                <p class="page-description">Add a single customer or import via CSV.</p>
            </div>
            <div>
                <a href="{{ url('/app/sharpfleet/admin/customers') }}" class="btn btn-secondary">
                    View Customers
                </a>
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

    {{-- =====================
         ADD SINGLE CUSTOMER
    ====================== --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Add Customer</h2>
            <p class="card-subtitle">
                Any format is fine (customer name, job number, or reference).
            </p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/app/sharpfleet/admin/customers') }}">
                @csrf

                {{-- Preserve trip context if coming from Trips --}}
                @if(request()->filled('trip_id'))
                    <input
                        type="hidden"
                        name="trip_id"
                        value="{{ request()->query('trip_id') }}"
                    >
                @endif

                @if(request()->filled('return'))
                    <input
                        type="hidden"
                        name="return"
                        value="{{ request()->query('return') }}"
                    >
                @endif

                <div class="form-group">
                    <label class="form-label">Customer name / reference</label>

                    <input
                        type="text"
                        name="name"
                        value="{{ $prefillName }}"
                        class="form-control"
                        maxlength="150"
                        placeholder="Customer name, job number, or reference"
                        {{ !$customersTableExists ? 'disabled' : '' }}
                        autofocus
                    >

                    @error('name')
                        <div class="text-error small">{{ $message }}</div>
                    @enderror
                </div>

                @if($branchesEnabled && $hasCustomerBranch)
                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        @if($branches->count() > 1)
                            <select name="branch_id" class="form-control" {{ !$customersTableExists ? 'disabled' : '' }}>
                                <option value="">Select branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($singleBranch)
                            <div class="hint-text">{{ $singleBranch->name }}</div>
                            <input type="hidden" name="branch_id" value="{{ $singleBranch->id }}">
                        @else
                            <div class="text-muted small">No branches available.</div>
                        @endif
                        @error('branch_id')
                            <div class="text-error small">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <button
                    type="submit"
                    class="btn btn-secondary btn-sm"
                    {{ !$customersTableExists ? 'disabled' : '' }}
                >
                    Add Customer
                </button>
            </form>
        </div>
    </div>

    {{-- =====================
         CSV IMPORT
    ====================== --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">CSV Import</h2>
            <p class="card-subtitle">
                Imports the first column as the customer name (one per row).
            </p>
        </div>
        <div class="card-body">
            <form
                method="POST"
                action="{{ url('/app/sharpfleet/admin/customers') }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="form-group">
                    <label class="form-label">CSV file</label>
                    <input
                        type="file"
                        name="customers_csv"
                        class="form-control"
                        accept=".csv,text/csv"
                        {{ !$customersTableExists ? 'disabled' : '' }}
                    >

                    @error('customers_csv')
                        <div class="text-error small">{{ $message }}</div>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="btn btn-secondary btn-sm"
                    {{ !$customersTableExists ? 'disabled' : '' }}
                >
                    Import CSV
                </button>
            </form>
        </div>
    </div>

    {{-- =====================
         TABLE NOT AVAILABLE
    ====================== --}}
    @if(!$customersTableExists)
        <div class="card">
            <div class="card-body">
                <p class="text-muted fst-italic mb-0">
                    Customer management is unavailable until the database table is created.
                </p>
            </div>
        </div>
    @endif
</div>

@endsection
