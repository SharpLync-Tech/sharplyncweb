{{-- resources/views/components/email-layout.blade.php --}}
@props(['title' => null])

@include('emails.layouts.sharplync', [
    'slot' => $slot,
    'title' => $title
])
