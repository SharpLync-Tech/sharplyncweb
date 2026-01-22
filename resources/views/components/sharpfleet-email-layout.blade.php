{{-- resources/views/components/sharpfleet-email-layout.blade.php --}}
@props(['title' => null])

@include('emails.layouts.sharpfleet', [
    'slot' => $slot,
    'title' => $title
])
