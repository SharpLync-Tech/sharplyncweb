@extends('layouts.sharpfleet')

@section('title', 'Add Customer')

@section('sharpfleet-content')

@php
    /**
     * Pull the prefill value from:
     * 1) old()  -> after validation error
     * 2) query  -> coming from Trips "Convert to customer"
     */
    $prefillName = old('name', request()->query('name', ''));
@endphp

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Add Customers</h1>
            <p class="page-description">
                Add a single customer or import via CSV.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ url('/app/sharpfleet/admin/customers') }}"
            >
                @csrf

                {{-- Preserve trip context if present --}}
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

                <div class="mb-3">
                    <label class="form-label">
                        Customer name / reference
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ $prefillName }}"
                        placeholder="Customer name, job number, or reference"
                        autofocus
                        required
                    >

                    <div class="form-text">
                        Any format is fine (customer name, job number, or reference).
                    </div>
                </div>

                <div class="mt-4">
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add Customer
                    </button>

                    <a
                        href="{{ url('/app/sharpfleet/admin/customers') }}"
                        class="btn btn-link"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection
