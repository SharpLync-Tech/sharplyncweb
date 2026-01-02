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

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-3">
        <div class="d-flex gap-2">
            <a class="btn btn-primary" href="/app/sharpfleet/admin/users/invite">Invite Driver</a>
            <a class="btn btn-secondary" href="/app/sharpfleet/admin/users/add">Add Driver</a>
            <a class="btn btn-secondary" href="/app/sharpfleet/admin/users/import">Import CSV</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
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
                                @if(($user->account_status ?? '') === 'pending')
                                    <span class="text-muted">Pending invite</span>
                                @else
                                    <span class="text-primary fw-bold">Active</span>
                                @endif
                            </td>
                            <td>
                                @if((int)($user->is_driver ?? 0) === 1)
                                    <span class="text-primary fw-bold">Enabled</span>
                                @else
                                    <span class="text-muted">Disabled</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="d-flex gap-2 justify-content-end">
                                    @if(($user->role ?? '') === 'driver' && ($user->account_status ?? '') === 'pending')
                                        <form method="POST" action="/app/sharpfleet/admin/users/{{ $user->id }}/resend-invite">
                                            @csrf
                                            <button class="btn btn-secondary btn-sm" type="submit">Resend invite</button>
                                        </form>
                                    @endif

                                    <a class="btn btn-secondary btn-sm" href="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/edit') }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
