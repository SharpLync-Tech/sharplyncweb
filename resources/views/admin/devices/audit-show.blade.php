@extends('admin.layouts.admin-layout')

@section('title', 'Audit Detail')

@section('content')
    <h2>Audit #{{ $audit->id }} – {{ $device->device_name ?? 'Device #'.$device->id }}</h2>

    <p><a href="{{ route('admin.devices.audits.index', $device->id) }}">&larr; Back to audit history</a></p>

    <pre style="background:#0b1220;color:#e5e7eb;padding:15px;border-radius:8px;overflow:auto;max-height:600px;">
{{ json_encode($audit->audit_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
    </pre>
@endsection
