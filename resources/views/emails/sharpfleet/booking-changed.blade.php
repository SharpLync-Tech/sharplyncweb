@php
    $oldVehicle = trim($vehicleOldName . ($vehicleOldReg ? ' (' . $vehicleOldReg . ')' : ''));
    $newVehicle = trim($vehicleNewName . ($vehicleNewReg ? ' (' . $vehicleNewReg . ')' : ''));

    $oldStartStr = $oldStart->format('d/m/Y H:i');
    $oldEndStr = $oldEnd->format('d/m/Y H:i');
    $newStartStr = $newStart->format('d/m/Y H:i');
    $newEndStr = $newEnd->format('d/m/Y H:i');
@endphp

<p>Hi {{ $driverName ?: 'there' }},</p>

@if($event === 'cancelled')
    <p>Your booking was cancelled by <strong>{{ $actorName }}</strong>.</p>
    <p>
        <strong>Vehicle:</strong> {{ $oldVehicle ?: '—' }}<br>
        <strong>Time:</strong> {{ $oldStartStr }} → {{ $oldEndStr }} ({{ $timezone }})
    </p>
@elseif($event === 'created')
    <p>A booking was created for you by <strong>{{ $actorName }}</strong>.</p>
    <p>
        <strong>Vehicle:</strong> {{ $newVehicle ?: '—' }}<br>
        <strong>Time:</strong> {{ $newStartStr }} → {{ $newEndStr }} ({{ $timezone }})
    </p>
@else
    <p>Your booking was changed by <strong>{{ $actorName }}</strong>.</p>
    <p>
        <strong>Vehicle:</strong> {{ $oldVehicle ?: '—' }}<br>
        <strong>Original time:</strong> {{ $oldStartStr }} → {{ $oldEndStr }} ({{ $timezone }})
    </p>
    <p>
        <strong>Vehicle:</strong> {{ $newVehicle ?: '—' }}<br>
        <strong>New time:</strong> {{ $newStartStr }} → {{ $newEndStr }} ({{ $timezone }})
    </p>
@endif

<p>If you have questions, please contact your administrator.</p>
