@extends('layouts.sharpfleet')

@section('title', 'Faults')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Faults</h1>
            <p class="page-description">Review incidents reported by drivers.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(!$faultsEnabled)
        <div class="alert alert-info">
            Incident / fault reporting is currently disabled for this company.
            Enable it in <a href="{{ url('/app/sharpfleet/admin/settings') }}">Settings</a>.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                @if(($faults ?? collect())->count() === 0)
                    <p class="text-muted fst-italic">No faults found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Created</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Summary</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($faults as $f)
                                    @php
                                        $driverName = trim(($f->user_first_name ?? '') . ' ' . ($f->user_last_name ?? ''));
                                        if ($driverName === '') {
                                            $driverName = $f->user_email ?? '—';
                                        }
                                        $vehicleLabel = trim(($f->vehicle_name ?? '') . ' ' . (($f->vehicle_registration_number ?? '') ? ('(' . $f->vehicle_registration_number . ')') : ''));
                                        if ($vehicleLabel === '') {
                                            $vehicleLabel = '—';
                                        }
                                        $summary = $f->title ?: '';
                                        if ($summary === '') {
                                            $summary = (string) ($f->description ?? '');
                                        }
                                        $summary = trim($summary);
                                        if (mb_strlen($summary) > 90) {
                                            $summary = mb_substr($summary, 0, 90) . '…';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($f->created_at)->format('M j, Y') }}</div>
                                            <div class="text-muted">{{ \Carbon\Carbon::parse($f->created_at)->format('g:i A') }}</div>
                                        </td>
                                        <td>{{ $vehicleLabel }}</td>
                                        <td>{{ $driverName }}</td>
                                        <td class="fw-bold">{{ ucfirst($f->severity ?? 'minor') }}</td>
                                        <td>{{ str_replace('_', ' ', ucfirst($f->status ?? 'open')) }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $summary ?: '—' }}</div>
                                            @if(!empty($f->trip_id))
                                                <div class="text-muted">Trip #{{ $f->trip_id }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ url('/app/sharpfleet/admin/faults/'.$f->id.'/status') }}" class="d-flex" style="gap: 8px; align-items: center;">
                                                @csrf
                                                <select name="status" class="form-control" style="max-width: 160px;">
                                                    @foreach(['open' => 'Open', 'in_review' => 'In review', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $key => $label)
                                                        <option value="{{ $key }}" {{ ($f->status ?? 'open') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@endsection
