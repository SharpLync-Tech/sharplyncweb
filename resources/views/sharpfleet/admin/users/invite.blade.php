@extends('layouts.sharpfleet')

@section('title', 'Invite Driver')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Invite Driver</h1>
        <p class="page-description">
            Send a one-time invitation link to a brand-new user. The link expires after 24 hours.
        </p>
    </div>

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
            <form method="POST" action="/app/sharpfleet/admin/users/invite">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Organisation</label>
                    <input class="form-control" value="{{ $organisation->name ?? '' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Driver email address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Send invitation</button>
                    <a href="/app/sharpfleet/admin/users" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
