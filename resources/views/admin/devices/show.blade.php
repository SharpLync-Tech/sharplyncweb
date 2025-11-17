@extends('admin.layouts.admin-layout')

@section('title', 'Device Details')

@section('content')
    <h2>Device: {{ $device->device_name ?? 'Unknown Device' }}</h2>

    <div class="admin-card" style="margin-bottom:20px;">
        <h3>Overview</h3>
        <p><strong>Manufacturer:</strong> {{ $device->manufacturer ?? '—' }}</p>
        <p><strong>Model:</strong> {{ $device->model ?? '—' }}</p>
        <p><strong>OS:</strong> {{ $device->os_version ?? '—' }}</p>
        <p><strong>RAM:</strong> {{ $device->total_ram_gb ?? '—' }} GB</p>
        <p><strong>CPU:</strong> {{ $device->cpu_model ?? '—' }} ({{ $device->cpu_cores }} cores / {{ $device->cpu_threads }} threads)</p>
        <p><strong>Storage:</strong> {{ $device->storage_size_gb ?? '—' }} GB ({{ $device->storage_used_percent ?? '—' }}% used)</p>
        <p><strong>Antivirus:</strong> {{ $device->antivirus ?? '—' }}</p>
        <p><strong>Last Audit:</strong> {{ optional($device->last_audit_at)->format('d M Y H:i') ?? '—' }}</p>
    </div>

    <div class="admin-card" style="margin-bottom:20px;">
        <h3>Customer Assignment</h3>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <p>
            <strong>Current:</strong>
            @if($device->customer)
                {{ $device->customer->name ?? 'Customer #'.$device->customer->id }}
            @else
                <span class="badge badge-warning">Unassigned</span>
            @endif
        </p>

        <form action="{{ route('admin.devices.assign', $device->id) }}" method="POST" style="margin-top:10px;max-width:400px;">
            @csrf
            <label for="customer_id">Assign to customer</label>
            <select name="customer_id" id="customer_id" class="form-control">
                <option value="">-- Select customer --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected($device->customer_id === $customer->id)>
                        {{ $customer->name ?? ('Customer #'.$customer->id) }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary" style="margin-top:10px;">
                Save Assignment
            </button>
        </form>
    </div>

    <div class="admin-card" style="margin-bottom:20px;">
        <h3>Installed Applications (latest audit)</h3>
        @if($device->apps->isEmpty())
            <p>No app data recorded for this device yet.</p>
        @else
            <table class="admin-table">
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
                        <td>{{ $app->version ?? '—' }}</td>
                        <td>{{ $app->publisher ?? '—' }}</td>
                        <td>{{ optional($app->installed_on)->format('d M Y') ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="admin-card">
        <h3>Audit History</h3>
        <a href="{{ route('admin.devices.audits.index', $device->id) }}">View full audit history</a>
    </div>
@endsection
