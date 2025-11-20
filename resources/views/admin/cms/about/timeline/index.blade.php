@extends('admin.layouts.admin-layout')

@section('title', 'About Page - Timeline')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>About Page Timeline</h2>
        <a href="{{ route('admin.cms.about.timeline.create') }}" class="btn btn-primary">Add Timeline Item</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Year</th>
                <th>Title</th>
                <th>Order</th>
                <th>Active</th>
                <th style="width:140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->year }}</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->sort_order }}</td>
                <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.about.timeline.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.about.timeline.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No timeline items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
