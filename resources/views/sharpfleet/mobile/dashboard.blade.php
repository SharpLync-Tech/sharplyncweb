@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Driver')

@section('content')
<section class="sf-mobile-dashboard">

    <h1 class="sf-mobile-title">Ready to Drive</h1>

    <p class="sf-mobile-subtitle">
        No active trip
    </p>

    <button
        class="sf-mobile-primary-btn"
        type="button"
        onclick="openStartTripSheet()"
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

@push('scripts')
<script>
    function openStartTripSheet() {
        document.body.classList.add('sf-sheet-open');
    }

    function closeStartTripSheet() {
        document.body.classList.remove('sf-sheet-open');
    }
</script>
@endpush
