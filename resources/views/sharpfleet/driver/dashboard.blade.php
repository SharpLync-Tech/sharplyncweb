@extends('layouts.sharpfleet')

@section('title', 'Driver Dashboard')

@section('sharpfleet-content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use App\Services\SharpFleet\CompanySettingsService;
    use App\Services\SharpFleet\BranchService;

    $user = session('sharpfleet.user');

    $settingsService = new CompanySettingsService($user['organisation_id']);
    $settings = $settingsService->all();

    $allowPrivateTrips = $settingsService->allowPrivateTrips();
    $faultsEnabled = $settingsService->faultsEnabled();
    $allowFaultsDuringTrip = $settingsService->allowFaultsDuringTrip();
    $companyTimezone = $settingsService->timezone();

    $odometerRequired = $settingsService->odometerRequired();
    $odometerAllowOverride = $settingsService->odometerAllowOverride();

    $manualTripTimesRequired = $settingsService->requireManualStartEndTimes();

    $safetyCheckEnabled = $settingsService->safetyCheckEnabled();
    $safetyCheckItems = $settingsService->safetyCheckItems();

    $branchesService = new BranchService();
    $branchesEnabled = $branchesService->branchesEnabled();
    $branchAccessEnabled = $branchesEnabled
        && $branchesService->vehiclesHaveBranchSupport()
        && $branchesService->userBranchAccessEnabled();
    $accessibleBranchIds = $branchAccessEnabled
        ? $branchesService->getAccessibleBranchIdsForUser((int) $user['organisation_id'], (int) $user['id'])
        : [];
@endphp

@if($activeTrip ?? false)
    {{-- existing active trip UI untouched --}}
@else

{{-- =========================
     START TRIP CARD
========================= --}}
<div class="card" id="startTripCard">
    <div class="card-header">
        <h3 class="card-title">Start a Trip</h3>
    </div>

    <div class="card-body">
        <form method="POST"
              action="/app/sharpfleet/trips/start"
              id="startTripForm">
            @csrf

            {{-- ALL EXISTING FIELDS UNCHANGED --}}
            {{-- vehicle, times, trip type, customer, odometer, etc --}}
            {{-- (unchanged code intentionally omitted for clarity in explanation only) --}}

            <button type="button"
                    id="startTripBtn"
                    class="btn btn-primary btn-full">
                Start Trip
            </button>
        </form>
    </div>
</div>

@endif

{{-- =========================
     SAFETY CHECK MODAL
========================= --}}
@if($safetyCheckEnabled)
<div id="safetyCheckModal" class="sf-modal-overlay" style="display:none;">
    <div class="sf-modal">
        <div class="sf-modal-header">
            <h3>Pre-Drive Safety Check</h3>
            <p class="hint-text">
                Complete the checks below before starting your trip.
            </p>
        </div>

        <div class="sf-modal-body">
            <ul class="sf-safety-list" id="sfSafetyList">
                @foreach($safetyCheckItems as $i => $item)
                    <li class="sf-safety-item" data-index="{{ $i }}">
                        <div class="sf-safety-row">
                            <button type="button"
                                    class="sf-safety-toggle"
                                    data-state="unchecked">
                                ⬜
                            </button>

                            <span class="sf-safety-label">
                                {{ $item['label'] ?? 'Safety item' }}
                            </span>
                        </div>

                        <div class="sf-safety-notes" style="display:none;">
                            <textarea
                                rows="2"
                                placeholder="e.g. Left headlight cracked but still working"></textarea>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div id="sfSafetyHint"
                 class="hint-text text-warning"
                 style="display:none;">
                Please review all safety items before continuing.
            </div>
        </div>

        <div class="sf-modal-footer">
            <button type="button"
                    id="sfLogFaultBtn"
                    class="btn btn-secondary btn-full"
                    style="display:none;">
                Log issue as vehicle fault
            </button>

            <button type="button"
                    id="sfConfirmSafetyBtn"
                    class="btn btn-primary btn-full"
                    disabled>
                Confirm Safety Check & Start Drive
            </button>

            <button type="button"
                    id="sfContinueBtn"
                    class="btn btn-link btn-full"
                    style="display:none;">
                Continue without logging
            </button>
        </div>
    </div>
</div>
@endif

{{-- =========================
     MODAL STYLES
========================= --}}
<style>
.sf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sf-modal {
    background: #fff;
    border-radius: 14px;
    width: 94%;
    max-width: 520px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.sf-modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.sf-modal-body {
    padding: 16px 20px;
    overflow-y: auto;
}

.sf-modal-footer {
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
}

.sf-safety-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sf-safety-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.sf-safety-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sf-safety-toggle {
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
}

.sf-safety-label {
    font-weight: 600;
}

.sf-safety-notes textarea {
    margin-top: 8px;
    width: 100%;
}
</style>

{{-- =========================
     MODAL SCRIPT
========================= --}}
<script>
(function () {
    const startBtn = document.getElementById('startTripBtn');
    const modal = document.getElementById('safetyCheckModal');
    if (!startBtn || !modal) return;

    const confirmBtn = document.getElementById('sfConfirmSafetyBtn');
    const faultBtn = document.getElementById('sfLogFaultBtn');
    const continueBtn = document.getElementById('sfContinueBtn');
    const hint = document.getElementById('sfSafetyHint');
    const items = modal.querySelectorAll('.sf-safety-item');

    startBtn.addEventListener('click', () => {
        modal.style.display = '';
        evaluate();
    });

    function evaluate() {
        let complete = true;
        let hasIssues = false;

        items.forEach(item => {
            const toggle = item.querySelector('.sf-safety-toggle');
            const notes = item.querySelector('textarea');
            const state = toggle.dataset.state;

            if (state === 'unchecked') complete = false;
            if (state === 'issue') {
                hasIssues = true;
                if (!notes.value.trim()) complete = false;
            }
        });

        confirmBtn.disabled = !complete;
        hint.style.display = complete ? 'none' : '';
        faultBtn.style.display = hasIssues ? '' : 'none';
        continueBtn.style.display = hasIssues ? '' : 'none';
    }

    items.forEach(item => {
        const toggle = item.querySelector('.sf-safety-toggle');
        const notesWrap = item.querySelector('.sf-safety-notes');
        const textarea = item.querySelector('textarea');

        toggle.addEventListener('click', () => {
            const state = toggle.dataset.state;

            if (state === 'unchecked') {
                toggle.textContent = '✅';
                toggle.dataset.state = 'ok';
                notesWrap.style.display = 'none';
            } else if (state === 'ok') {
                toggle.textContent = '⚠️';
                toggle.dataset.state = 'issue';
                notesWrap.style.display = '';
            } else {
                toggle.textContent = '✅';
                toggle.dataset.state = 'ok';
                notesWrap.style.display = 'none';
            }
            evaluate();
        });

        textarea.addEventListener('input', evaluate);
    });

    confirmBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        document.getElementById('startTripForm').submit();
    });
})();
</script>

@endsection
