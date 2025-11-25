@extends('admin.layouts.base')

@section('title', 'SMS Logs')

@section('content')

<div class="container mt-4">

    <h2 class="mb-4">SMS Logs</h2>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Admin</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Code</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>

                            <td>{{ $log->admin_name ?? '—' }}</td>

                            <td>
                                @if($log->customer_profile_id)
                                    <a href="/admin/customers/{{ $log->customer_profile_id }}">
                                        {{ optional($log->customerProfile)->company_name ?? 'Customer' }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>

                            <td>{{ $log->phone }}</td>

                            <td style="max-width: 300px;">
                                {{ \Illuminate\Support\Str::limit($log->message, 40) }}
                            </td>

                            <td>
                                @if($log->status === 'success')
                                    <span class="badge bg-success">Success</span>
                                @else
                                    <span class="badge bg-danger">{{ $log->status }}</span>
                                @endif
                            </td>

                            <td>{{ $log->verification_code ?? '—' }}</td>
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
