@extends('admin.layouts.admin-layout')

@section('title', 'Unassigned Devices')

@section('content')

<h2>Unassigned Devices</h2>
<p class="text-muted">Devices not yet linked to a customer.</p>

<table class="table">
    <thead>
        <tr>
            <th>Device</th>
            <th>Model</th>
            <th>Last Audit</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($devices as $device)
        <tr>
            <td>
                <a href="{{ route('admin.devices.show', $device->id) }}">
                    {{ $device->device_name }}
                </a>
            </td>
            <td>{{ $device->manufacturer }} {{ $device->model }}</td>
            <td>{{ optional($device->last_audit_at)->format('d M Y H:i') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">No unassigned devices.</td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $devices->links() }}

@endsection
