@extends('admin.layouts.admin-layout')

@section('title', 'Dashboard')

@section('content')
    {{-- ======== HEADER AREA / PROFILE ========= --}}
    <div class="admin-top-bar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:25px;">
        <h2>Welcome back, {{ session('admin_user')['displayName'] ?? 'SharpLync Admin' }}</h2>
        <div class="profile-box" style="display:flex;align-items:center;gap:10px;">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(session('admin_user')['displayName'] ?? 'SharpLync Admin') }}&background=0A2A4D&color=fff&size=40" 
                 alt="Profile" style="border-radius:50%;width:40px;height:40px;box-shadow:0 0 6px rgba(0,0,0,0.15);">
            <span style="font-weight:600;color:#0A2A4D;">{{ session('admin_user')['displayName'] ?? 'SharpLync Admin' }}</span>
        </div>
    </div>

    <p>This is your secure admin dashboard.</p>

    {{-- ======== QUICK LINKS ========= --}}
    <div class="admin-card mt-3">
        <h3>Quick Links</h3>
        <ul style="margin-top:10px;line-height:1.8;">
            <li><a href="#" onclick="openModal()">Manage Pulse Feed</a></li>
            <li><a href="#">View Logs</a></li>
            <li><a href="#">System Settings</a></li>
        </ul>
    </div>

    {{-- ======== SHARPLYNC MODAL ========= --}}
    <div id="sharpModal" class="modal-overlay">
        <div class="modal">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2>SharpLync Notice</h2>
            <p>Hey {{ session('admin_user')['givenName'] ?? 'Admin' }}, this is a live SharpLync modal demo. You can reuse this modal for confirmations, alerts, or forms across the admin portal.</p>
            <div class="modal-actions">
                <button class="btn btn-accent" onclick="closeModal()">Got it</button>
                <button class="btn btn-primary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    {{-- ======== MODAL SCRIPT ========= --}}
    <script>
        function openModal() {
            document.getElementById('sharpModal').classList.add('active');
        }
        function closeModal() {
            document.getElementById('sharpModal').classList.remove('active');
        }
    </script>
@endsection