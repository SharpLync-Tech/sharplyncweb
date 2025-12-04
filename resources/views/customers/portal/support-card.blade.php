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

        <a href="{{ route('customer.teamviewer.download') }}" class="btn btn-teal">
            Download SharpLync Remote Support
        </a>
    </div>
</div>
