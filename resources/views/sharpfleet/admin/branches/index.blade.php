@extends('layouts.sharpfleet')

@section('title', 'Branches')

@section('sharpfleet-content')

<div class="max-w-900 mx-auto mt-4">
    <div class="page-header">
        <div class="flex-between" style="gap: 12px;">
            <div>
                <h1 class="page-title">Branches</h1>
                <p class="page-description mb-0">Branches define your operational locations and their timezones.</p>
            </div>
            <div class="btn-group">
                @if(($branchesEnabled ?? false) === true)
                    <a class="btn btn-secondary" href="{{ url('/app/sharpfleet/admin/branches/create') }}">Add branch</a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <strong>Please fix the errors below.</strong>
            <ul class="mb-0" style="margin-top: 8px; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(($branchesEnabled ?? false) !== true)
        <div class="card">
            <p class="mb-2"><strong>Branches are not enabled yet.</strong></p>
            <p class="mb-0">Run the phpMyAdmin installer SQL at <strong>docs/sharpfleet-branches.sql</strong>, then refresh this page.</p>
        </div>
    @else
        <div class="card">
            @if(($branches ?? collect())->count() === 0)
                <p class="mb-0">No branches found.</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Timezone</th>
                                <th>Default</th>
                                <th>Status</th>
                                <th style="width: 120px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branches as $b)
                                @php
                                    $isDefault = (int) ($b->is_default ?? 0) === 1;
                                    $isActive = !property_exists($b, 'is_active') ? true : ((int) ($b->is_active ?? 1) === 1);
                                @endphp
                                <tr>
                                    <td>{{ (string) ($b->name ?? '') }}</td>
                                    <td>{{ (string) ($b->timezone ?? '') }}</td>
                                    <td>{{ $isDefault ? 'Yes' : '—' }}</td>
                                    <td>{{ $isActive ? 'Active' : 'Archived' }}</td>
                                    <td class="text-right">
                                        <a class="btn-sf-navy btn-sm" href="{{ url('/app/sharpfleet/admin/branches/' . (int) $b->id . '/edit') }}">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>

@endsection
