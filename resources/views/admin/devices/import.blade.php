@extends('admin.layouts.admin-layout')

@section('title', 'Import Device Audit')

@section('content')

<h2>Import Device Audit</h2>
<p class="text-muted">Upload a JSON audit file generated from the SharpLync audit script.</p>

@if ($errors->any())
    <div class="alert" style="background:#ffe5e5; border-left:4px solid #c62828;">
        {{ $errors->first() }}
    </div>
@endif

@if (session('status'))
    <div class="alert">{{ session('status') }}</div>
@endif

<div class="admin-card" style="max-width:500px;">

    <form action="{{ route('admin.devices.import.process') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        <label style="font-weight:600;">Audit JSON File</label>
        <input type="file"
               name="audit_file"
               class="form-control"
               accept=".json"
               required>

        <p class="form-help">Upload the JSON file from your PowerShell system audit.</p>

        <button class="btn btn-primary mt-2">Import Device Audit</button>

    </form>

</div>

@endsection
