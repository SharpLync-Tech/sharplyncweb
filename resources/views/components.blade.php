@extends('layouts.app')

@section('title', 'SharpLync Component Library')

@section('content')
<div class="container">
    <h1 class="mt-2">SharpLync Component Library</h1>
    <p>Welcome to the SharpLync Design System. This page demonstrates all reusable UI components and their current styles defined in <strong>app.css</strong>.</p>

    <!-- =====================================
         TYPOGRAPHY
         ===================================== -->
    <section class="section">
        <h2>Typography</h2>
        <h1>Heading 1 – Page Title</h1>
        <h2>Heading 2 – Section Title</h2>
        <h3>Heading 3 – Subsection</h3>
        <p>This is a standard paragraph of text. <strong>Bold</strong> and <em>italic</em> styles are supported. 
        You can also <a href="#">link text</a> in SharpLync blue with hover color change.</p>
    </section>

    <!-- =====================================
         BUTTONS
         ===================================== -->
    <section class="section">
        <h2>Buttons</h2>
        <div style="display:flex; flex-wrap:wrap; gap:1rem;">
            <button class="btn">Primary</button>
            <button class="btn-secondary">Secondary</button>
            <button class="btn-accent">Accent</button>
            <button class="btn" disabled>Disabled</button>
        </div>
    </section>

    <!-- =====================================
         CARDS
         ===================================== -->
    <section class="section">
        <h2>Cards</h2>
        <div class="card">
            <h3 class="card-title">Example Card Title</h3>
            <p>This is an example card. Cards have shadows, rounded corners, and a teal accent border. Use them for dashboards, data previews, or announcements.</p>
            <button class="btn-secondary">Learn More</button>
        </div>
    </section>

    <!-- =====================================
         FORMS
         ===================================== -->
    <section class="section">
        <h2>Form Fields</h2>
        <form>
            <div class="card">
                <label for="name">Name</label><br>
                <input id="name" type="text" placeholder="Enter your name"
                    style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #ccc; margin-top:0.3rem;">

                <label for="email" class="mt-2">Email</label><br>
                <input id="email" type="email" placeholder="Enter your email"
                    style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #ccc; margin-top:0.3rem;">

                <label for="message" class="mt-2">Message</label><br>
                <textarea id="message" placeholder="Your message"
                    style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #ccc; margin-top:0.3rem; height:100px;"></textarea>

                <div class="mt-2 text-center">
                    <button type="submit" class="btn">Submit</button>
                    <button type="reset" class="btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </section>

    <!-- =====================================
         TABLES
         ===================================== -->
    <section class="section">
        <h2>Tables</h2>
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Wi-Fi Upgrade</td><td>Jannie Brits</td><td>Active</td><td><button class="btn-secondary">View</button></td></tr>
                <tr><td>Trend Micro Deployment</td><td>AITC IT</td><td>Completed</td><td><button class="btn-secondary">View</button></td></tr>
                <tr><td>SharpLync Portal</td><td>Development</td><td>In Progress</td><td><button class="btn-secondary">Edit</button></td></tr>
            </tbody>
        </table>
    </section>

    <!-- =====================================
         MODALS
         ===================================== -->
    <section class="section">
        <h2>Modal Demo</h2>
        <button class="btn" onclick="openModal('componentModal')">Open Modal</button>
    </section>

    <!-- The Modal -->
    <div id="componentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>SharpLync Modal Demo</h3>
                <button class="modal-close" onclick="closeModal('componentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>This modal uses the SharpLync theme and includes smooth fade & slide transitions.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal('componentModal')">Close</button>
                <button class="btn">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection
