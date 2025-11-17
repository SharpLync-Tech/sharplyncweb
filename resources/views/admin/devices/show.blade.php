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
        <select name="customer_profile_id"
                id="customerSelect"
                class="form-control">
            <option value="">-- Select --</option>

            <option value="__new__">-- Create New Customer --</option>

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


{{-- ============================================================
     NEW CUSTOMER CREATION MODAL
============================================================ --}}
<div id="newCustomerModal" class="modal-overlay">
    <div class="modal wider-modal">
        <span class="modal-close" onclick="closeNewCustomerModal()">&times;</span>

        <h2>Create New Customer</h2>

        <form id="newCustomerForm"
              method="POST"
              action="{{ route('admin.devices.assign', $device->id) }}">
            @csrf

            <input type="hidden" name="customer_profile_id" value="__new__">

            <!-- Customer Type -->
            <label style="font-weight:600;">Customer Type</label>
            <select name="cust_type" id="custTypeSelect" class="form-control" required>
                <option value="">-- Select Type --</option>
                <option value="individual">Individual</option>
                <option value="business">Business / Company</option>
            </select>

            <!-- INDIVIDUAL -->
            <div id="individualFields" style="display:none; margin-top:15px;">
                <label>First Name</label>
                <input type="text" class="form-control" name="ind_first_name">

                <label style="margin-top:10px;">Last Name</label>
                <input type="text" class="form-control" name="ind_last_name">

                <label style="margin-top:10px;">Email</label>
                <input type="email" class="form-control" name="ind_email">
            </div>

            <!-- BUSINESS -->
            <div id="businessFields" style="display:none; margin-top:15px;">
                <label>Business Name</label>
                <input type="text" class="form-control" name="biz_name">

                <label style="margin-top:10px;">Primary Contact First Name</label>
                <input type="text" class="form-control" name="biz_first_name">

                <label style="margin-top:10px;">Primary Contact Last Name</label>
                <input type="text" class="form-control" name="biz_last_name">

                <label style="margin-top:10px;">Contact Email</label>
                <input type="email" class="form-control" name="biz_email">
            </div>

            <!-- Welcome Email -->
            <div style="margin-top:15px;">
                <label>
                    <input type="checkbox" name="send_welcome_email" value="1">
                    Send Welcome Email
                </label>
            </div>

            <button type="submit" class="btn btn-primary mt-2" style="margin-top:20px;">
                Create Customer & Assign Device
            </button>
        </form>
    </div>
</div>

<style>
    .wider-modal {
        width: 600px !important;
        max-width: 95%;
    }
</style>

{{-- JS --}}
<script>
document.getElementById('customerSelect').addEventListener('change', function () {
    if (this.value === '__new__') {
        openNewCustomerModal();
        this.value = "";
    }
});

function openNewCustomerModal() {
    document.getElementById('newCustomerModal').classList.add('active');
}

function closeNewCustomerModal() {
    document.getElementById('newCustomerModal').classList.remove('active');
}

document.getElementById('custTypeSelect').addEventListener('change', function () {
    let type = this.value;

    document.getElementById('individualFields').style.display =
        (type === 'individual') ? 'block' : 'none';

    document.getElementById('businessFields').style.display =
        (type === 'business') ? 'block' : 'none';
});
</script>

@endsection
