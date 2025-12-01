{{-- 
  Partial: customers/portal/account-card.blade.php
  Usage: Account Summary card on right column
--}}

<div class="cp-activity-card cp-account-card">
    <h4>Account Summary</h4>
    <p>Review your account status, services, and billing details.</p>

    <div class="cp-account-footer">
        <a href="{{ route('customer.account') }}" 
           class="cp-btn cp-small-btn cp-teal-btn">
            View Account
        </a>
    </div>
</div>
