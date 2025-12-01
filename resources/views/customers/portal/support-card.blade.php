{{-- 
  Partial: customers/portal/support-card.blade.php
  Usage: Support card on right column
--}}

<div class="cp-activity-card cp-support-card">
    <h4>Support</h4>
    <p>Need help? View support tickets or connect for remote assistance.</p>

    <div class="cp-support-footer">
        <a href="{{ route('customer.support.index') }}" 
           class="cp-btn cp-small-btn cp-teal-btn">
            Open Support
        </a>

        <a href="{{ URL::temporarySignedRoute('customer.teamviewer.download', now()->addMinutes(5)) }}"
           class="cp-btn cp-small-btn cp-teal-btn">
            Download Quick Support
        </a>
    </div>
</div>
