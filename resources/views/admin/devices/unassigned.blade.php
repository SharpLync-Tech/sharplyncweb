@extends('admin.layouts.admin-layout')

@section('title', 'Unassigned Devices')

@section('content')
    <h2>Unassigned Devices</h2>
    <p class="text-muted">Devices that are not yet linked to a CRM customer.</p>

    @include('admin.devices._device-table', ['devices' => $devices])
@endsection
