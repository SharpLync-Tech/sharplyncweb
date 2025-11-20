@extends('admin.layouts.admin-layout')

@section('title', 'Pages')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Pages</h2>
        <a href="{{ route('admin.cms.pages.create') }}" class="btn btn-primary">Add Page</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Slug</th>
                <th>Title</th>
                <th>Active</th>
                <th style="width:140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pages as $page)
            <tr>
                <td>{{ $page->slug }}</td>
                <td>{{ $page->title }}</td>
                <td>{{ $page->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.pages.edit', $page->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.pages.destroy', $page->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this page?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No pages found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
