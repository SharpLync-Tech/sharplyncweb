{{--
  Page: admin.support.tickets.show
  Description: Admin ticket detail + conversation + notes
--}}

@extends('admin.layouts.admin-layout')

@section('title', 'Ticket #' . $ticket->id . ' — ' . $ticket->subject)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-tickets.css') }}?v=1.0">
@endpush

@section('content')
<div class="container-fluid admin-ticket-show-page mt-3">

    @include('admin.support.tickets.partials.header')

    <div class="row g-3 mt-1">

        {{-- Left column: meta + notes --}}
        <div class="col-12 col-lg-4">
            @include('admin.support.tickets.partials.details')

            @include('admin.support.tickets.partials.notes')
        </div>

        {{-- Right column: conversation + reply --}}
        <div class="col-12 col-lg-8">
            @include('admin.support.tickets.partials.conversation')

            @include('admin.support.tickets.partials.reply')
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-tickets.js') }}?v=1.0"></script>
@endpush
