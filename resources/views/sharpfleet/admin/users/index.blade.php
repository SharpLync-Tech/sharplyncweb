@extends('layouts.sharpfleet')

@section('title', 'Users')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <p class="page-description">
            Manage driver access for users in your organisation. Enabling driver access lets an admin use the Driver View.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Driver access</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ trim($user->first_name.' '.$user->last_name) }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                                @if((int)($user->is_driver ?? 0) === 1)
                                    <span class="text-primary fw-bold">Enabled</span>
                                @else
                                    <span class="text-muted">Disabled</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a class="btn btn-secondary btn-sm" href="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/edit') }}">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
