@extends('layouts.sharpfleet')

@section('title', 'About SharpFleet')

@section('sharpfleet-content')
<div class="hero">
    <h1>About<br><span class="highlight">SharpFleet</span></h1>
    <p class="mb-0">
        SharpFleet is the fleet management module inside SharpLync.
    </p>
</div>

<div class="card">
    <div class="card-body">
        <h3 class="section-title">What this module does</h3>
        <ul class="mb-0" style="margin:0; padding-left: 18px;">
            <li>Manage vehicles, bookings, trips, and faults</li>
            <li>Support driver workflows (including offline trip capture)</li>
            <li>Provide reporting and operational visibility</li>
        </ul>
    </div>
</div>
@endsection
