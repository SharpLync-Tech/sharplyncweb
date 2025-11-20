@extends('admin.layouts.admin-layout')

@section('title', 'Pulse Feed')

@section('content')
<div class="container py-4">
    <h2>Pulse Feed</h2>

    <a href="{{ route('admin.pulse.create') }}" class="btn btn-primary mb-3">Add Pulse Item</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Active</th>
                <th>Created</th>
                <th style="width: 140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $pulse)
                <tr>
                    <td>{{ $pulse->title }}</td>
                    <td>{{ $pulse->is_active ? 'Yes' : 'No' }}</td>
                    <td>{{ $pulse->created_at?->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.pulse.edit', $pulse->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        <form action="{{ route('admin.pulse.destroy', $pulse->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete this item?')" 
                                    class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No pulse items yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
