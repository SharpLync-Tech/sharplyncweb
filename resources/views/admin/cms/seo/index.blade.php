@extends('admin.layouts.admin-layout')

@section('title', 'SEO Meta')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>SEO Meta</h2>
        <a href="{{ route('admin.cms.seo.create') }}" class="btn btn-primary">Add SEO Entry</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Page</th>
                <th>Meta Title</th>
                <th>Active</th>
                <th style="width:130px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meta as $entry)
            <tr>
                <td>{{ $entry->page_slug }}</td>
                <td>{{ $entry->meta_title }}</td>
                <td>{{ $entry->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.seo.edit', $entry->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.cms.seo.destroy', $entry->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Delete this SEO entry?')" class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No SEO entries yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
