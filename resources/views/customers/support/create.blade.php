{{-- resources/views/customers/support/create.blade.php --}}
{{-- SharpLync Support Module V1: Create ticket --}}

@extends('customers.layouts.customer-layout')

@section('title', 'New Support Request')

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/support/support.css') }}">
@endpush

@section('content')
<div class="support-wrapper">
    <div class="support-header">
        <h1 class="support-title">New Support Request</h1>
        <p class="support-subtitle">
            Tell us what’s going on and we’ll get you back on track.
        </p>
        <a href="{{ route('customer.support.index') }}" class="support-link-back">
            Back to my tickets
        </a>
    </div>

    @if ($errors->any())
        <div class="support-alert support-alert-error">
            <strong>Please check the form:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customer.support.tickets.store') }}" method="POST" class="support-form">
        @csrf

        <div class="support-form-group">
            <label for="subject" class="support-label">Subject</label>
            <input type="text"
                   id="subject"
                   name="subject"
                   class="support-input"
                   value="{{ old('subject') }}"
                   required
                   maxlength="255">
        </div>

        <div class="support-form-group">
            <label for="priority" class="support-label">Priority</label>
            <select id="priority" name="priority" class="support-select" required>
                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium (default)</option>
                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent - system down</option>
            </select>
        </div>

        <div class="support-form-group">
            <label for="message" class="support-label">Describe the issue</label>
            <textarea id="message"
                      name="message"
                      class="support-textarea"
                      rows="8"
                      required>{{ old('message') }}</textarea>
            <p class="support-help">
                Include any error messages, what you were doing at the time, and how many people are affected.
            </p>
        </div>

        <div class="support-form-actions">
            <button type="submit" class="support-btn-primary">
                Submit Support Request
            </button>
            <a href="{{ route('customer.support.index') }}" class="support-btn-primary">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ secure_asset('js/support/support.js') }}"></script>
@endpush
