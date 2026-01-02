@extends('layouts.sharpfleet')

@section('title', 'Add Driver')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Add Driver</h1>
        <p class="page-description">
            Create a driver account and send a one-time invitation link. The link expires after 24 hours.
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

    <div class="card" style="max-width: 720px;">
        <div class="card-body">
            <form method="POST" action="/app/sharpfleet/admin/users/add">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Organisation</label>
                    <input class="form-control" value="{{ $organisation->name ?? '' }}" disabled>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">First name (optional)</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
                    </div>
                    <div>
                        <label class="form-label">Last name (optional)</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Driver email address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Add and send invite</button>
                    <a href="/app/sharpfleet/admin/users" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
