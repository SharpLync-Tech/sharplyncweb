@extends('layouts.sharpfleet')

@section('title', 'Edit Customer')

@section('sharpfleet-content')

@php
    $customersTableExists = $customersTableExists ?? true;
    $branches = $branches ?? collect();
    $branchesEnabled = $branchesEnabled ?? false;
    $hasCustomerBranch = $hasCustomerBranch ?? false;
    $singleBranch = $branches->count() === 1 ? $branches->first() : null;
@endphp

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Edit Customer</h1>
                <p class="page-description">Update the customer name or archive the customer.</p>
            </div>
            <div>
                <a href="{{ url('/app/sharpfleet/admin/customers') }}" class="btn btn-secondary">Back to Customers</a>
            </div>
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

    @if (!$customersTableExists)
        <div class="card">
            <div class="card-body">
                <p class="text-muted fst-italic mb-0">Customer management is unavailable until the database table is created.</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Customer Details</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/app/sharpfleet/admin/customers/' . $customer->id) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Customer name / reference</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $customer->name ?? '') }}"
                               class="form-control"
                               maxlength="150"
                               placeholder="Customer name, job number, or reference">
                        @error('name')
                            <div class="text-error small">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($branchesEnabled && $hasCustomerBranch)
                        <div class="form-group">
                            <label class="form-label">Branch</label>
                            @if($branches->count() > 1)
                                <select name="branch_id" class="form-control">
                                    <option value="">Select branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ (string) old('branch_id', $customer->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>
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
                    <button type="submit" class="btn-sf-navy btn-sm">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Archive</h2>
                <p class="card-subtitle">Archived customers are hidden from the list.</p>
            </div>
            <div class="card-body">
                    <form method="POST"
                        action="{{ url('/app/sharpfleet/admin/customers/' . $customer->id . '/archive') }}"
                        data-sf-confirm
                        data-sf-confirm-title="Archive customer"
                        data-sf-confirm-message="Archive this customer? They will be hidden from the list."
                        data-sf-confirm-text="Archive"
                        data-sf-confirm-variant="danger">
                    @csrf
                    <button type="submit" class="btn btn-danger">Archive Customer</button>
                </form>
            </div>
        </div>
    @endif
</div>

@endsection
