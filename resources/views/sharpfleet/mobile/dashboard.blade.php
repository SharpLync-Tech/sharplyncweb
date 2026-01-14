@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Driver')

@section('content')
<section class="sf-mobile-dashboard">

    <h1 class="sf-mobile-title">Ready to Drive</h1>

    <p class="sf-mobile-subtitle">
        No active trip
    </p>

    <button
        type="button"
        class="sf-mobile-primary-btn"
        data-sheet-open="start-trip"
    >
        Start Drive
    </button>

    <button class="sf-mobile-secondary-btn" type="button">
        Report Vehicle Issue
    </button>

</section>

{{-- Start Trip Sheet --}}
@include('sharpfleet.mobile.sheets.start-trip')
@endsection
