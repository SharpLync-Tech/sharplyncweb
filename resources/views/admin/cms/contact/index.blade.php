@extends('admin.layouts.admin-layout')

@section('title', 'Contact Information')

@section('content')
<div class="container py-4">

    <h2>Contact Information</h2>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered mt-4">
        <tr>
            <th>Phone</th>
            <td>{{ $contact->phone ?? '—' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $contact->email ?? '—' }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>{{ $contact->address ?? '—' }}</td>
        </tr>
        <tr>
            <th>Hours</th>
            <td>{{ $contact->hours ?? '—' }}</td>
        </tr>
        <tr>
            <th>Facebook</th>
            <td>{{ $contact->facebook_url ?? '—' }}</td>
        </tr>
        <tr>
            <th>Instagram</th>
            <td>{{ $contact->instagram_url ?? '—' }}</td>
        </tr>
        <tr>
            <th>LinkedIn</th>
            <td>{{ $contact->linkedin_url ?? '—' }}</td>
        </tr>
        <tr>
            <th>Twitter</th>
            <td>{{ $contact->twitter_url ?? '—' }}</td>
        </tr>
        <tr>
            <th>YouTube</th>
            <td>{{ $contact->youtube_url ?? '—' }}</td>
        </tr>
    </table>

    <a href="{{ route('admin.cms.contact.edit', $contact->id) }}" class="btn btn-primary mt-3">Edit Contact Info</a>

</div>
@endsection
