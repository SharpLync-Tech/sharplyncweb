@extends('admin.layouts.admin-layout')

@section('title', 'Edit Contact Information')

@section('content')
<div class="container py-4">

    <h2>Edit Contact Information</h2>

    <form action="{{ route('admin.cms.contact.update', $contact->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ $contact->phone }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" name="email" class="form-control" value="{{ $contact->email }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2" class="form-control">{{ $contact->address }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Business Hours</label>
            <textarea name="hours" rows="2" class="form-control">{{ $contact->hours }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Google Map Embed</label>
            <textarea name="google_map_embed" rows="2" class="form-control">{{ $contact->google_map_embed }}</textarea>
        </div>

        <hr>

        <h4>Social Links</h4>

        <div class="mb-3">
            <label class="form-label">Facebook URL</label>
            <input type="text" name="facebook_url" class="form-control" value="{{ $contact->facebook_url }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Instagram URL</label>
            <input type="text" name="instagram_url" class="form-control" value="{{ $contact->instagram_url }}">
        </div>

        <div class="mb-3">
            <label class="form-label">LinkedIn URL</label>
            <input type="text" name="linkedin_url" class="form-control" value="{{ $contact->linkedin_url }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Twitter URL</label>
            <input type="text" name="twitter_url" class="form-control" value="{{ $contact->twitter_url }}">
        </div>

        <div class="mb-3">
            <label class="form-label">YouTube URL</label>
            <input type="text" name="youtube_url" class="form-control" value="{{ $contact->youtube_url }}">
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.contact.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
