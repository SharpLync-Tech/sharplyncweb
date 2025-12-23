@extends('layouts.base')

@section('title', 'SharpFleet Admin Registration')

@section('content')
<div style="max-width:600px;margin:60px auto;">
    <h1>Register SharpFleet Admin</h1>
    <p>Form only — no saving yet.</p>

    <form>
        <div>
            <label>Name</label>
            <input type="text">
        </div>

        <div style="margin-top:10px;">
            <label>Email</label>
            <input type="email">
        </div>

        <button style="margin-top:20px;" disabled>
            Save (coming soon)
        </button>
    </form>
</div>
@endsection
