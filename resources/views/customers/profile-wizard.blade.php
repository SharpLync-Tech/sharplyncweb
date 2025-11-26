@extends('customers.layouts.customer-layout')

@section('title', 'Update Profile')

@section('content')

<div class="cp-card profile-wizard-container fade-in">

    {{-- Progress Bar --}}
    <div class="wizard-progress">
        <div class="wizard-step" data-step="1">Personal</div>
        <div class="wizard-step" data-step="2">Business</div>
        <div class="wizard-step" data-step="3">Mailing</div>
        <div class="wizard-step" data-step="4">Billing</div>
        <div class="wizard-step" data-step="5">Review</div>
        <div class="wizard-progress-bar"></div>
    </div>

    {{-- Step Content Wrapper --}}
    <div id="wizard-steps">

        {{-- STEP 1 — PERSONAL --}}
        @include('customers.profile_wizard.step1')

        {{-- STEP 2 — BUSINESS --}}
        @include('customers.profile_wizard.step2')

        {{-- STEP 3 — MAILING ADDRESS --}}
        @include('customers.profile_wizard.step3')

        {{-- STEP 4 — BILLING ADDRESS --}}
        @include('customers.profile_wizard.step4')

        {{-- STEP 5 — REVIEW --}}
        @include('customers.profile_wizard.step5')

    </div>

    {{-- Navigation Buttons --}}
    <div class="wizard-nav">
        <button id="wizard-prev" class="cp-btn cp-navy-btn" disabled>Back</button>
        <button id="wizard-next" class="cp-btn cp-teal-btn">Next</button>
        <button id="wizard-save" class="cp-btn cp-teal-btn" style="display:none;">Save Profile</button>
    </div>

</div>

@endsection

@section('scripts')
@include('customers.profile_wizard.wizard-js')
@endsection
