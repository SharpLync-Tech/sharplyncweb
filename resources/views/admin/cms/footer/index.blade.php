@extends('admin.layouts.admin-layout')

@section('title', 'Footer Links')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Footer Links</h2>
        <a href="{{ route('admin.cms.footer.create') }}" class="btn btn-primary">Add Footer Link</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Label</th>
                <th>URL</th>
                <th>Column</th>
                <th>Order</th>
                <th>Active</th>
                <th style="width:140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($links as $link)
            <tr>
                <td>{{ $link->label }}</td>
                <td>{{ $link->url }}</td>
                <td>{{ $link->column }}</td>
                <td>{{ $link->sort_order }}</td>
                <td>{{ $link->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.footer.edit', $link->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.footer.destroy', $link->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this link?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">No footer links found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
