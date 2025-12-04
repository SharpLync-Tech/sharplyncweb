{{-- 
    Page: customers/portal/remote-support.blade.php
    Purpose: SharpLync Remote Support download page with SSPIN security warning
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'SharpLync Remote Support')

@section('content')
<div class="sl-remote-support-page">

    <div class="sl-remote-support-card">

        {{-- CLOSE BUTTON --}}
        <a href="{{ route('customer.portal') }}" class="sl-remote-support-close-btn">×</a>

        <div class="sl-remote-support-header">
            <div class="sl-remote-support-icon">🖥️</div>
            <div>
                <h1 class="sl-remote-support-title">SharpLync Remote Support</h1>
                <p class="sl-remote-support-subtitle">
                    Download our secure remote support tool so a SharpLync technician can safely assist you.
                </p>
            </div>
        </div>

        <div class="sl-remote-support-body">
            <div class="sl-remote-support-text">
                <p>
                    This tool allows a SharpLync technician to temporarily view your screen and help resolve
                    issues on your device. You are always in control and can close the session at any time.
                </p>
            </div>

            <div class="sl-remote-support-warning">
                <div class="sl-remote-support-warning-icon">⚠</div>
                <div class="sl-remote-support-warning-text">
                    <h2>SECURITY WARNING</h2>
                    <p>
                        Before using this tool, confirm you are talking to an official <strong>SharpLync</strong> representative.
                    </p>
                    <p>
                        Ask them to provide <strong>your SSPIN number</strong>.
                    </p>
                    <p>
                        If they cannot give it to you, <strong>DO NOT</strong> grant remote access.
                        Hang up and call our official number.
                    </p>
                </div>
            </div>

            <div class="sl-remote-support-meta">
                <p class="sl-remote-support-version">
                    Version: <strong>SharpLync QuickSupport 1.0 (Windows 64-bit)</strong>
                </p>
                <p class="sl-remote-support-note">
                    Only download and run this tool if you are currently in contact with SharpLync about a support issue.
                </p>
            </div>

            <div class="sl-remote-support-actions">
                <a href="{{ route('customer.teamviewer.download') }}" class="sl-remote-support-button">
                    <span class="sl-remote-support-button-icon">⬇</span>
                    <span>Download SharpLync Remote Support</span>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
