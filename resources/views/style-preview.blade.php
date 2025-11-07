@extends('layouts.app')

@section('title', 'SharpLync Style Preview')

@section('content')
<div class="container">
    <h1 class="mt-2">SharpLync Style Preview</h1>
    <p>This page showcases components styled with <strong>app.css</strong>.</p>

    <!-- Buttons -->
    <section class="section">
        <h2>Buttons</h2>
        <button class="btn">Primary Button</button>
        <button class="btn-secondary">Secondary Button</button>
    </section>

    <!-- Dropdown -->
    <section class="section">
        <h2>Dropdown</h2>
        <select class="rounded" style="padding: 0.6rem 1rem; border: 1px solid #ccc;">
            <option>Select an option</option>
            <option>Option A</option>
            <option>Option B</option>
            <option>Option C</option>
        </select>
    </section>

    <!-- Form Fields -->
    <section class="section">
        <h2>Form Fields</h2>
        <form>
            <div class="card">
                <label for="name">Name</label><br>
                <input id="name" type="text" placeholder="Enter your name" 
                       style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #ccc; margin-top: 0.3rem;">

                <label for="email" class="mt-2">Email</label><br>
                <input id="email" type="email" placeholder="Enter your email" 
                       style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #ccc; margin-top: 0.3rem;">

                <label for="message" class="mt-2">Message</label><br>
                <textarea id="message" placeholder="Enter your message" 
                          style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #ccc; margin-top: 0.3rem; height: 100px;"></textarea>

                <div class="mt-2 text-center">
                    <button type="button" class="btn" onclick="openModal('testModal')">Open Modal</button>
                    <button type="submit" class="btn-secondary">Submit</button>
                </div>
            </div>
        </form>
    </section>
</div>

<!-- Modal Test -->
<div id="testModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>SharpLync Modal Test</h3>
            <button class="modal-close" onclick="closeModal('testModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>This is a sample modal window styled with the SharpLync theme.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('testModal')">Close</button>
            <button class="btn">Save</button>
        </div>
    </div>
</div>
@endsection