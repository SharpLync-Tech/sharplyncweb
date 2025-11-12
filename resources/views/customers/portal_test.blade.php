@extends('customer-layout-test')

@section('title', 'Dashboard')

@section('content')

    <div class="cp-pagehead">
        <h2>Customer Details</h2>
    </div>

    <div class="cp-card cp-dashboard-grid">
        {{-- Left Column: Customer Profile Details (The Card on the left) --}}
        <div class="cp-profile-card">
            <div class="cp-profile-header">
                {{-- Placeholder for a user image (requires custom CSS) --}}
                <div class="cp-avatar"></div> 
                <div class="cp-name-group">
                    <h3>Jane Doe</h3>
                    <p class="cp-member-status">Premium Member</p>
                </div>
            </div>
            
            <div class="cp-contact-details">
                <p><strong>Email:</strong> <a href="mailto:jane.doe@email.com">jane.doe@email.com</a></p>
                <p><strong>Phone:</strong> +1 (555) 123-567</p>
                <p><strong>Address:</strong> 123 Maple Street, Anytown, CA 1034</p>
                <p class="cp-member-since"><strong>Member Since:</strong> January 2020</p>
            </div>
            
            <button class="cp-btn cp-edit-profile">Edit Profile</button>
        </div>

        {{-- Right Column: Recent Activity (The Stacked Cards on the right) --}}
        <div class="cp-activity-column">
            
            {{-- Latest Invoice Card --}}
            <div class="cp-activity-card cp-invoice-card">
                <h4>Latest Invoice</h4>
                <p class="cp-invoice-date">October 26, 2023</p>
                <div class="cp-invoice-footer">
                    <span class="cp-invoice-amount">$120.50</span>
                    <a href="#" class="cp-btn cp-small-btn cp-teal-btn">View Details</a>
                </div>
            </div>

            {{-- Support Tickets Card --}}
            <div class="cp-activity-card cp-ticket-card">
                <h4>Support Tickets (Open)</h4>
                <p>Ticket ID: Service Interruption</p>
                <p class="cp-ticket-status">Status: In Progress</p>
                <div class="cp-ticket-footer">
                    {{-- Note: Used a fake progress bar element here for illustration --}}
                    <div class="cp-progress-bar">
                        <div class="cp-progress-fill" style="width: 75%;"></div>
                        <span class="cp-progress-label">75% of 100GB</span>
                    </div>
                    <a href="#" class="cp-btn cp-small-btn cp-track-btn">Track Ticket</a>
                </div>
            </div>

            {{-- Plan Details Card --}}
            <div class="cp-activity-card cp-plan-card">
                <h4>Plan Details</h4>
                <div class="cp-plan-footer">
                    <span class="cp-current-plan">Current Plan: <strong>Platinum</strong></span>
                    <a href="#" class="cp-upgrade-btn">Upgrade Plan</a>
                </div>
            </div>
        </div>
    </div>
    
@endsection