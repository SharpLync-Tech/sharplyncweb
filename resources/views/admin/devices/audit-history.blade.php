@extends('admin.layouts.admin-layout')

@section('title', 'Audit History')

@section('content')
    <h2>Audit History – {{ $device->device_name ?? 'Device #'.$device->id }}</h2>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($audits as $audit)
            <tr>
                <td>{{ $audit->id }}</td>
                <td>{{ $audit->created_at->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ route('admin.devices.audits.show', [$device->id, $audit->id]) }}">
                        View
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No audits recorded yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $audits->links() }}
@endsection
