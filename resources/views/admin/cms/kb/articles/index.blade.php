@extends('admin.layouts.admin-layout')

@section('title', 'Knowledge Base Articles')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Knowledge Base Articles</h2>
        <a href="{{ route('admin.cms.kb.articles.create') }}" class="btn btn-primary">Add Article</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Published</th>
                <th>Active</th>
                <th style="width:140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($articles as $article)
            <tr>
                <td>{{ $article->title }}</td>
                <td>{{ $article->category->name ?? '—' }}</td>
                <td>{{ $article->published_at ? $article->published_at->format('d M Y') : '—' }}</td>
                <td>{{ $article->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.cms.kb.articles.edit', $article->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.cms.kb.articles.destroy', $article->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Delete this article?')" class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No articles found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
