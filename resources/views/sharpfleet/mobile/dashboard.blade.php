@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Driver')

@section('content')
<section class="sf-mobile-dashboard">

    <h1 class="sf-mobile-title">Ready to Drive</h1>

    <p class="sf-mobile-subtitle">
        No active trip
    </p>

    {{-- Start Drive --}}
    <button
        class="sf-mobile-primary-btn"
        type="button"
        onclick="openStartTripSheet()"
    >
        Start Drive
    </button>

    {{-- Report issue (later) --}}
    <button class="sf-mobile-secondary-btn" type="button">
        Report Vehicle Issue
    </button>

</section>

{{-- ===============================
     Start Trip Sheet
================================ --}}
@include('sharpfleet.mobile.sheets.start-trip')

@endsection

@push('scripts')
<script>
/*
|--------------------------------------------------------------------------
| Mobile Sheet Control – Start Trip
|--------------------------------------------------------------------------
| No framework
| No dependencies
| Safe for PWA
*/

function openStartTripSheet() {
    const sheet = document.getElementById('sf-start-trip-sheet');
    const backdrop = document.getElementById('sf-sheet-backdrop');

    if (!sheet || !backdrop) return;

    sheet.classList.remove('sf-sheet-hidden');
    sheet.classList.add('sf-sheet-visible');

    backdrop.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeStartTripSheet() {
    const sheet = document.getElementById('sf-start-trip-sheet');
    const backdrop = document.getElementById('sf-sheet-backdrop');

    if (!sheet || !backdrop) return;

    sheet.classList.remove('sf-sheet-visible');
    sheet.classList.add('sf-sheet-hidden');

    backdrop.style.display = 'none';
    document.body.style.overflow = '';
}

/* Close when backdrop tapped */
document.addEventListener('DOMContentLoaded', () => {
    const backdrop = document.getElementById('sf-sheet-backdrop');
    if (!backdrop) return;

    backdrop.addEventListener('click', closeStartTripSheet);
});
</script>
@endpush
