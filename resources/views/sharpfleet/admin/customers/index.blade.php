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
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('customers'))
        <div class="alert alert-danger">
            {{ $errors->first('customers') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Add Customer</h2>
            <p class="card-subtitle">Any format is fine (Enter a customer name, job number, or reference that makes sense to you).</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/app/sharpfleet/admin/customers') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Customer name / reference</label>
                    <input type="text" name="name" class="form-control" maxlength="150" placeholder="Customer name, job number, or reference" {{ !$customersTableExists ? 'disabled' : '' }}>
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
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
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-secondary" {{ !$customersTableExists ? 'disabled' : '' }}>Import CSV</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Customer List</h2>
        </div>
        <div class="card-body">
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $c)
                                <tr>
                                    <td class="fw-bold">{{ $c->name }}</td>
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
