{{-- 
  Page: customers/portal.blade.php
  Version: v2.2 (Bottom Logout + Glass Header Optimization)
  Last updated: 13 Nov 2025 by Max (ChatGPT)
  Description:
  - Removes header logout
  - Adds logout power icon centered at bottom of main card
  - Fully responsive; mobile layout tested
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Customer Portal')

@section('content')
<div class="portal-header">
    <div class="portal-header-inner">
        <h2>Account Portal</h2>
    </div>
</div>

<div class="portal-wrapper">
    <div class="portal-main-card">
        <div class="portal-tabs">
            <button class="active" data-tab="details">
                <img src="/images/details.png" alt="Details Icon"> Details
            </button>
            <button data-tab="financial">
                <img src="/images/financial.png" alt="Financial Icon"> Financial
            </button>
            <button data-tab="security">
                <img src="/images/security.png" alt="Security Icon"> Security
            </button>
            <button data-tab="documents">
                <img src="/images/documents.png" alt="Documents Icon"> Documents
            </button>
            <button data-tab="support">
                <img src="/images/support.png" alt="Support Icon"> Support
            </button>
        </div>

        {{-- ===== TAB CONTENTS ===== --}}
        <div id="details" class="portal-content active">
            <h3>Account Details</h3>
            <p>View and update your personal and company information.</p>
            <a href="#" class="btn-primary">Edit Profile</a>
        </div>

        <div id="financial" class="portal-content">
            <h3>Financial</h3>
            <p>Billing and payment history will appear here.</p>
        </div>

        <div id="security" class="portal-content">
            <h3>Security Settings</h3>
            <p>Manage 2FA, password, and account security preferences.</p>
        </div>

        <div id="documents" class="portal-content">
            <h3>Documents</h3>
            <p>Access invoices, quotes, and uploaded files here.</p>
        </div>

        <div id="support" class="portal-content">
            <h3>Support</h3>
            <p>Submit support tickets or chat with SharpLync support.</p>
        </div>

        {{-- ===== PORTAL FOOTER ===== --}}
        <p style="text-align:center; margin-top:2rem; font-size:0.9rem;">
            SharpLync – Old School Support, <span class="highlight">Modern Results</span>
        </p>

        {{-- ===== LOGOUT BUTTON (BOTTOM ICON) ===== --}}
        <form action="{{ route('customer.logout') }}" method="POST" class="logout-inline portal-logout">
            @csrf
            <button type="submit" title="Log out"
    style="
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2CBFAE;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        transition: all 0.25s ease;
    "
    onmouseover="this.style.background='rgba(44,191,174,0.25)'; this.style.borderColor='rgba(44,191,174,0.6)'; this.style.transform='translateY(-2px)';"
    onmouseout="this.style.background='rgba(255,255,255,0.12)'; this.style.borderColor='rgba(255,255,255,0.25)'; this.style.transform='translateY(0)';"
>
    ⏻
</button>

        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tab switching logic
    document.querySelectorAll('.portal-tabs button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.portal-tabs button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.portal-content').forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            const tabId = btn.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
</script>
@endsection