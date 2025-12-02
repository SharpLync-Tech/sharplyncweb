{{-- ========================================================= --}}
{{-- SSPIN Success Modal                                      --}}
{{-- File: portal/modals/sspin-success-modal.blade.php        --}}
{{-- ========================================================= --}}

<div id="cp-sspin-success-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet">

        <div style="text-align:center; padding:2rem 1rem;">
            
            {{-- Teal glowing tick --}}
            <div style="
                width:80px;
                height:80px;
                margin:0 auto 1rem;
                border-radius:50%;
                background:#2CBFAE;
                display:flex;
                align-items:center;
                justify-content:center;
                box-shadow:0 0 20px rgba(44,191,174,0.6);
            ">
                <span style="font-size:40px; color:white;">✔</span>
            </div>

            <h3 style="margin-bottom:.5rem;">SSPIN Updated</h3>
            <p>Your Support PIN was updated successfully.</p>

            <button id="cp-sspin-success-close" 
                    class="cp-btn cp-teal-btn"
                    style="margin-top:1.5rem;">
                OK
            </button>

        </div>

    </div>
</div>
