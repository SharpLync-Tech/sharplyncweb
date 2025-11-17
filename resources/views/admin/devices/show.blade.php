@extends('admin.layouts.admin-layout')

@section('title', 'Device Details')

@section('content')

<h2>Device: {{ $device->device_name }}</h2>

{{-- Overview --}}
<div class="admin-card">
    <h3>Overview</h3>

    <p><strong>Manufacturer:</strong> {{ $device->manufacturer ?? '—' }}</p>
    <p><strong>Model:</strong> {{ $device->model ?? '—' }}</p>
    <p><strong>OS:</strong> {{ $device->os_version ?? '—' }}</p>
    <p><strong>RAM:</strong> {{ $device->total_ram_gb ?? '—' }} GB</p>
    <p><strong>CPU:</strong> {{ $device->cpu_model ?? '—' }} ({{ $device->cpu_cores }} cores, {{ $device->cpu_threads }} threads)</p>
    <p><strong>Storage:</strong> {{ $device->storage_size_gb }} GB ({{ $device->storage_used_percent }}% used)</p>
    <p><strong>Antivirus:</strong> {{ $device->antivirus }}</p>
    <p><strong>Last Audit:</strong> {{ optional($device->last_audit_at)->format('d M Y H:i') }}</p>
</div>

        {{-- Customer Assignment --}}
        <div class="admin-card">
            <h3>Customer Assignment</h3>

            @if(session('status'))
                <div class="alert">{{ session('status') }}</div>
            @endif

            <p><strong>Current:</strong>
                @if($device->customerProfile)
                    {{ $device->customerProfile->business_name }}
                @else
                    <span class="badge badge-off">Unassigned</span>
                @endif
            </p>

            <form method="POST"
                action="{{ route('admin.devices.assign', $device->id) }}"
                style="max-width:400px;">
                @csrf

                <label>Select customer</label>
                <select name="customer_profile_id" class="form-control">
                    <option value="">-- Select --</option>

                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}"
                            @selected($device->customer_profile_id === $cust->id)>
                            {{ $cust->business_name }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-primary" style="margin-top:10px;">
                    Save Assignment
                </button>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="admin-card" style="border:2px solid #ffdddd;">
            <h3 style="color:#b30000;">Danger Zone</h3>
            <p>Deleting this device will remove:</p>
            <ul>
                <li>All audit logs</li>
                <li>All installed apps records</li>
                <li>The device record itself</li>
            </ul>

            <form action="{{ route('admin.devices.destroy', $device->id) }}"
                method="POST"
                onsubmit="return confirm('Are you sure you want to permanently delete this device and all audit data?');">

                @csrf
                @method('DELETE')

                <button class="btn btn-danger" style="margin-top:10px;">
                    Delete Device
                </button>
            </form>
        </div>


{{-- Apps --}}
<div class="admin-card">
    <h3>Installed Applications</h3>

    @if($device->apps->isEmpty())
        <p>No application data recorded.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Version</th>
                    <th>Publisher</th>
                    <th>Installed On</th>
                </tr>
            </thead>
            <tbody>
            @foreach($device->apps as $app)
                <tr>
                    <td>{{ $app->name }}</td>
                    <td>{{ $app->version }}</td>
                    <td>{{ $app->publisher }}</td>
                    <td>{{ optional($app->installed_on)->format('d M Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Audit history --}}
<div class="admin-card">
    <h3>Audit History</h3>
    <a href="{{ route('admin.devices.audits.index', $device->id) }}">View audit history</a>
</div>

@endsection
