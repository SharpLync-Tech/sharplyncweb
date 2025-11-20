@extends('admin.layouts.admin-layout')

@section('title', 'About – Sections')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>About Page – Sections</h2>
        <a href="{{ route('admin.cms.about.sections.create') }}" class="btn btn-primary">Add Section</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Heading</th>
                <th>Sort Order</th>
                <th>Active</th>
                <th style="width: 140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sections as $section)
            <tr>
                <td>{{ $section->heading }}</td>
                <td>{{ $section->sort_order }}</td>
                <td>{{ $section->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.about.sections.edit', $section->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.about.sections.destroy', $section->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Delete this section?')" class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No sections found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
