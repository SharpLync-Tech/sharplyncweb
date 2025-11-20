@extends('admin.layouts.admin-layout')


@section('title', 'CMS Menu Items')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Menu Items</h2>
        <a href="{{ route('admin.cms.menu.create') }}" class="btn btn-primary">Add Menu Item</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Label</th>
                <th>URL</th>
                <th>Order</th>
                <th>Active</th>
                <th>New Tab</th>
                <th style="width:140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->label }}</td>
                <td>{{ $item->url }}</td>
                <td>{{ $item->sort_order }}</td>
                <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                <td>{{ $item->open_in_new_tab ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.menu.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.menu.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this menu item?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">No menu items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
