@extends('admin.layouts.admin-layout')

@section('title', 'About Page - Values')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>About Page Values</h2>
        <a href="{{ route('admin.cms.about.values.create') }}" class="btn btn-primary">Add Value</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Title</th>
                <th>Icon</th>
                <th>Order</th>
                <th>Active</th>
                <th style="width:140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($values as $value)
            <tr>
                <td>{{ $value->title }}</td>
                <td>
                    @if($value->icon_path)
                        <img src="{{ asset('storage/'.$value->icon_path) }}" width="40" alt="">
                    @else
                        <span class="text-muted">None</span>
                    @endif
                </td>
                <td>{{ $value->sort_order }}</td>
                <td>{{ $value->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.about.values.edit', $value->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.about.values.destroy', $value->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this value?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No values found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
