@extends('layouts.sharpfleet')

@section('title', 'SharpFleet - Login')

@section('sharpfleet-content')
<div class="card max-w-400 mx-auto mt-4">
    <div class="card-header">
        <h2 class="card-title">SharpFleet Login</h2>
    </div>

    <form method="POST" action="/app/sharpfleet/login">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    @if($errors->any())
        <div class="alert alert-error mt-3">
            <strong>Please fix the errors below.</strong>
            <ul class="mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
