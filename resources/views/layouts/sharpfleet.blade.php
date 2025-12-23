@extends('layouts.base')

{{-- 
    SharpFleet Layout Wrapper
    Purpose:
    - Isolate SharpFleet UI from main CMS
    - Allow future SharpFleet-specific nav, logo, branding
--}}

@section('content')

    {{-- SharpFleet Header / Nav (minimal for now) --}}
    <div style="background:#0A2A4D;color:white;padding:14px 20px;">
        <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-weight:600;">
                SharpFleet
            </div>

            <div style="font-size:14px;">
                @if(session()->has('sharpfleet.user'))
                    {{ session('sharpfleet.user.name') }}
                @endif
            </div>
        </div>
    </div>

    {{-- SharpFleet Page Content --}}
    <main>
        @yield('sharpfleet-content')
    </main>

@endsection
