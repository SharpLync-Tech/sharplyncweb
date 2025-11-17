@extends('admin.layouts.admin-layout')

@section('title', 'Devices')

@section('content')

<h2>All Devices</h2>
<p class="text-muted">All audited devices across CRM.</p>

<table class="table">
    <thead>
        <tr>
            <th>Device</th>
            <th>Model</th>
            <th>Customer</th>
            <th>Antivirus</th>
            <th>Last Audit</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($devices as $device)
        <tr>
            <td>
                <a href="{{ route('admin.devices.show', $device->id) }}">
                    {{ $device->device_name ?? 'Unknown' }}
                </a>
            </td>
            <td>{{ $device->manufacturer }} {{ $device->model }}</td>
            <td>
                @if($device->customerProfile)
                    {{ $device->customerProfile->business_name }}
                @else
                    <span class="badge badge-off">Unassigned</span>
                @endif
            </td>
            <td>{{ $device->antivirus ?? '—' }}</td>
            <td>{{ optional($device->last_audit_at)->format('d M Y H:i') ?? '—' }}</td>
            <td>
                @if($device->customer_profile_id)
                    <span class="badge badge-on">Assigned</span>
                @else
                    <span class="badge badge-off">Unassigned</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6">No devices found yet.</td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $devices->links() }}

@endsection
