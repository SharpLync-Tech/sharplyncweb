@extends('layouts.app')

@section('title', 'SharpLync Mobile Preview')

@section('content')
<div class="container">
    <h1>Mobile Preview</h1>
    <p>Test how the SharpLync layout responds to smaller screens.</p>

    <section class="section">
        <h2>Buttons</h2>
        <button class="btn">Primary</button>
        <button class="btn-secondary">Secondary</button>
        <button class="btn-accent">Accent</button>
    </section>

    <section class="section">
        <h2>Form</h2>
        <div class="card">
            <label for="name">Name</label><br>
            <input id="name" type="text" placeholder="Your name"><br><br>

            <label for="email">Email</label><br>
            <input id="email" type="email" placeholder="Your email"><br><br>

            <label for="message">Message</label><br>
            <textarea id="message" placeholder="Your message"></textarea><br><br>

            <button class="btn" type="button">Submit</button>
        </div>
    </section>

    <section class="section">
        <h2>Table</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>John Smith</td><td>Engineer</td><td>Active</td></tr>
                <tr><td>Sarah Lee</td><td>Designer</td><td>Active</td></tr>
            </tbody>
        </table>
    </section>
</div>
@endsection
