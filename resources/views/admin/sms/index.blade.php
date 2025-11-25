@extends('admin.layouts.admin-layout')

@section('title', 'Send Verification SMS')

@section('content')

<div class="container mt-4">

    <h2 class="mb-3">Send Verification SMS</h2>

    @if(isset($customer))
        <div class="alert alert-info">
            <strong>Customer:</strong> {{ $customer->company ?? '' }} <br>
            <strong>Contact:</strong> {{ $customer->full_name ?? '' }} <br>
            <strong>Phone:</strong> {{ $prefillPhone }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST" action="{{ url('/admin/support/sms/send') }}">
                @csrf

                <input type="hidden" name="customer_id" value="{{ $customer->id ?? '' }}">

                <!-- Phone -->
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone', $prefillPhone) }}"
                           placeholder="04XXXXXXXX" required>
                </div>

                <!-- Auto-generate Code -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="use_code" id="use_code" value="1"
                        @if(old('use_code')) checked @endif>
                    <label class="form-check-label" for="use_code">
                        Auto-generate 6-digit verification code
                    </label>
                </div>

                <!-- Custom Message -->
                <div class="mb-3">
                    <label class="form-label">Custom Message (ignored if code is auto-generated)</label>
                    <textarea name="message" class="form-control" rows="3"
                        placeholder="Type your message here...">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    Send SMS
                </button>

            </form>

        </div>
    </div>

    @if(session('response'))
        <div class="card mt-4 shadow-sm">
            <div class="card-body">
                <h5>API Response</h5>
                <pre class="bg-light p-3 rounded">{{ json_encode(session('response'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

</div>

@endsection
