@extends('layouts.sharpfleet')

@section('title', 'Add Customers')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Add Customers</h1>
                <p class="page-description">Add a single customer or import via CSV.</p>
            </div>
            <div>
                <a href="{{ url('/app/sharpfleet/admin/customers') }}" class="btn btn-secondary">View Customers</a>
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
            <h2 class="card-title">Add Customer</h2>
            <p class="card-subtitle">Any format is fine (customer name, job number, or reference).</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/app/sharpfleet/admin/customers') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Customer name / reference</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="form-control"
                           maxlength="150"
                           placeholder="Customer name, job number, or reference"
                           {{ !$customersTableExists ? 'disabled' : '' }}>
                    @error('name')
                        <div class="text-error small">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary" {{ !$customersTableExists ? 'disabled' : '' }}>Add Customer</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">CSV Import</h2>
            <p class="card-subtitle">Imports the first column as the customer name (one per row).</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/app/sharpfleet/admin/customers') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">CSV file</label>
                    <input type="file" name="customers_csv" class="form-control" accept=".csv,text/csv" {{ !$customersTableExists ? 'disabled' : '' }}>
                    @error('customers_csv')
                        <div class="text-error small">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-secondary" {{ !$customersTableExists ? 'disabled' : '' }}>Import CSV</button>
            </form>
        </div>
    </div>

    @if(!$customersTableExists)
        <div class="card">
            <div class="card-body">
                <p class="text-muted fst-italic mb-0">Customer management is unavailable until the database table is created.</p>
            </div>
        </div>
    @endif
</div>

@endsection
