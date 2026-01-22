@extends('layouts.sharpfleet')

@section('title', 'Import Drivers (CSV)')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Import Drivers (CSV)</h1>
        <p class="page-description">
            Upload a CSV to create pending driver accounts. You can send invitations from the Users page when you are ready.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/app/sharpfleet/admin/users/import" enctype="multipart/form-data">
        @csrf

        <div class="card" style="max-width: 720px;">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Organisation</label>
                    <input class="form-control" value="{{ $organisation->name ?? '' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">CSV file</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="form-control" required>
                    <div class="form-hint">
                        Supported columns: <strong>email</strong>, <strong>first_name</strong>, <strong>last_name</strong>.
                        If there is no header row, the order must be: email, first_name, last_name.
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3" style="max-width: 720px;">
            <button type="submit" class="btn btn-primary">Import drivers</button>
            <a href="/app/sharpfleet/admin/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
