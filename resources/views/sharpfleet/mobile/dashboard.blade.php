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

{{-- =================================================
     Start Trip Sheet
     Only load when data exists
================================================= --}}
@isset($vehicles)
    @include('sharpfleet.mobile.sheets.start-trip')
@endisset

@endsection

@push('scripts')
<script>
function openStartTripSheet() {
    const sheet = document.getElementById('sf-start-trip-sheet');
    const backdrop = document.getElementById('sf-sheet-backdrop');

    if (!sheet || !backdrop) {
        alert('Start Trip data not loaded yet');
        return;
    }

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

document.addEventListener('DOMContentLoaded', () => {
    const backdrop = document.getElementById('sf-sheet-backdrop');
    if (!backdrop) return;
    backdrop.addEventListener('click', closeStartTripSheet);
});
</script>
@endpush
