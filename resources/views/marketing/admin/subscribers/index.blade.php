<!-- Marketing Page: Subscribers -->
@extends('marketing.admin.layout')

@section('content')

@if(session('error'))
    <div class="card" style="background:#ffecec;border:1px solid #ffb3b3;">
        <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="card" style="background:#e6f7ef;border:1px solid #b7e2c9;">
        <strong>Success:</strong> {{ session('success') }}
    </div>
@endif

<div class="card">
    <h2 style="margin-top:0;">Add Subscriber</h2>
    <form method="POST" action="{{ route('marketing.admin.subscribers.store') }}" style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
        @csrf
        <div>
            <label style="display:block;margin-bottom:6px;font-weight:600;">First Name</label>
            <input type="text" name="first_name" value="{{ old('first_name') }}"
                   style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-weight:600;">Email</label>
            <input type="email" name="email" required value="{{ old('email') }}"
                   style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-weight:600;">Brand</label>
            <select name="brand" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
                <option value="sl" {{ old('brand') === 'sl' ? 'selected' : '' }}>SharpLync</option>
                <option value="sf" {{ old('brand') === 'sf' ? 'selected' : '' }}>SharpFleet</option>
            </select>
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-weight:600;">Status</label>
            <select name="status" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
                <option value="subscribed" {{ old('status') === 'subscribed' ? 'selected' : '' }}>Subscribed</option>
                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="unsubscribed" {{ old('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
            </select>
        </div>
        <div style="grid-column:1 / -1;">
            <button type="submit" class="btn-primary">Add Subscriber</button>
        </div>
    </form>
</div>

<div class="card">
    <h2 style="margin-top:0;">Recent Subscribers</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Brand</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($subscribers as $subscriber)
            <tr>
                <form method="POST" action="{{ route('marketing.admin.subscribers.update', $subscriber->id) }}">
                    @csrf
                    <td>{{ $subscriber->id }}</td>
                    <td>
                        <input type="text" name="first_name" value="{{ $subscriber->first_name }}" style="width:140px;padding:6px 8px;border:1px solid #ddd;border-radius:6px;">
                    </td>
                    <td>
                        <input type="email" name="email" value="{{ $subscriber->email }}" style="width:220px;padding:6px 8px;border:1px solid #ddd;border-radius:6px;">
                    </td>
                    <td>
                        <select name="brand" style="padding:6px 8px;border:1px solid #ddd;border-radius:6px;">
                            <option value="sl" {{ $subscriber->brand === 'sl' ? 'selected' : '' }}>SL</option>
                            <option value="sf" {{ $subscriber->brand === 'sf' ? 'selected' : '' }}>SF</option>
                        </select>
                    </td>
                    <td>
                        <select name="status" style="padding:6px 8px;border:1px solid #ddd;border-radius:6px;">
                            <option value="subscribed" {{ $subscriber->status === 'subscribed' ? 'selected' : '' }}>subscribed</option>
                            <option value="pending" {{ $subscriber->status === 'pending' ? 'selected' : '' }}>pending</option>
                            <option value="unsubscribed" {{ $subscriber->status === 'unsubscribed' ? 'selected' : '' }}>unsubscribed</option>
                        </select>
                    </td>
                    <td>{{ $subscriber->created_at ? $subscriber->created_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <button type="submit" class="btn-send">Save</button>
                    </td>
                </form>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
