@extends('admin.layouts.admin-layout')

@section('title', 'SMS Logs')

@section('content')

<div class="container mt-4">

    <h2 class="mb-4">SMS Logs</h2>

    {{-- SEARCH + FILTER BAR --}}
    <form method="GET" action="{{ route('admin.support.sms.logs') }}" class="card p-3 mb-4 shadow-sm">

        <div class="row g-3">

            {{-- SEARCH --}}
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="q"
                       class="form-control"
                       value="{{ $q }}"
                       placeholder="Phone, message, code, name...">
            </div>

            {{-- TYPE --}}
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    <option value="verification" {{ $type=='verification' ? 'selected' : '' }}>Verification</option>
                    <option value="general" {{ $type=='general' ? 'selected' : '' }}>General</option>
                </select>
            </div>

            {{-- STATUS --}}
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="success" {{ $status=='success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ $status=='failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            {{-- DATE FROM --}}
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control">
            </div>

            {{-- DATE TO --}}
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control">
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Apply Filters</button>
            <a href="{{ route('admin.support.sms.logs') }}" class="btn btn-secondary">Reset</a>
        </div>

    </form>

    {{-- LOG TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Admin</th>
                        <th>Recipient</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Code</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($logs as $log)
                        <tr>
                            <!-- DATE -->
                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>

                            <!-- TYPE -->
                            <td>
                                @if($log->customer_profile_id)
                                    <span class="badge bg-primary">Verification</span>
                                @else
                                    <span class="badge bg-info text-dark">General</span>
                                @endif
                            </td>

                            <!-- ADMIN -->
                            <td>{{ highlight($log->admin_name, $q) }}</td>

                            <!-- RECIPIENT / CUSTOMER -->
                            <td>
                                @if($log->customer_profile_id)
                                    <a href="/admin/customers/{{ $log->customer_profile_id }}">
                                        {{ highlight(optional($log->customerProfile)->business_name 
                                            ?? optional($log->customerProfile)->authority_contact, $q) }}
                                    </a>
                                @else
                                    {{ highlight($log->recipient_name ?? '—', $q) }}
                                @endif
                            </td>

                            <!-- PHONE -->
                            <td>{{ highlight($log->phone, $q) }}</td>

                            <!-- MESSAGE -->
                            <td style="max-width:300px;">
                                {!! highlight(\Illuminate\Support\Str::limit($log->message, 60), $q) !!}
                            </td>

                            <!-- STATUS -->
                            <td>
                                @if($log->status === 'success')
                                    <span class="badge bg-success">Success</span>
                                @else
                                    <span class="badge bg-danger">{{ $log->status }}</span>
                                @endif
                            </td>

                            <!-- CODE -->
                            <td>{{ highlight($log->verification_code ?? '—', $q) }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>

</div>

@endsection
