@extends('admin.layouts.admin-layout')

@section('title', 'Services')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Services</h2>
        <a href="{{ route('admin.cms.services.create') }}" class="btn btn-primary">Add Service</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Title</th>
                <th>Short Description</th>
                <th>Order</th>
                <th>Active</th>
                <th style="width:140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr>
                <td>{{ $service->title }}</td>
                <td>{{ Str::limit($service->short_description, 50) }}</td>
                <td>{{ $service->sort_order }}</td>
                <td>{{ $service->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.services.edit', $service->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.services.destroy', $service->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this service?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No services found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
