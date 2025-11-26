@extends('emails.contact.layout')

@section('content')
    <h2 style="margin:0 0 16px 0; font-size:20px; color:#0A2A4D;">
        We’ve received your message
    </h2>

    <p style="margin:0 0 12px 0; font-size:14px; color:#1A3152;">
        Hi {{ $name }},
    </p>

    <p style="margin:0 0 12px 0; font-size:14px; color:#1A3152;">
        Thanks for getting in touch with <strong>SharpLync</strong>. We’ve received your message and a member of our team will review it and get back to you as soon as possible.
    </p>

    <p style="margin:0 0 16px 0; font-size:14px; color:#1A3152;">
        Here’s a summary of what you sent us:
    </p>

    <table cellpadding="0" cellspacing="0" style="width:100%; font-size:14px; margin-bottom:18px;">
        <tr>
            <td style="padding:4px 0; width:110px; font-weight:bold; color:#0A2A4D;">Subject</td>
            <td style="padding:4px 0; color:#1A3152;">{{ $subject }}</td>
        </tr>
        @if(!empty($phone))
            <tr>
                <td style="padding:4px 0; font-weight:bold; color:#0A2A4D;">Phone</td>
                <td style="padding:4px 0; color:#1A3152;">{{ $phone }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:4px 0; font-weight:bold; color:#0A2A4D;">Email</td>
            <td style="padding:4px 0; color:#1A3152;">{{ $email }}</td>
        </tr>
    </table>

    <h3 style="margin:0 0 8px 0; font-size:16px; color:#0A2A4D;">
        Your message
    </h3>
    <p style="margin:0 0 18px 0; font-size:14px; line-height:1.6; color:#1A3152; white-space:pre-line;">
        {{ $user_message }}
    </p>

    <p style="margin:0; font-size:13px; color:#6b7a90;">
        If you didn’t submit this message or believe this was an error, please email info@sharplync.com.au and let us know.
    </p>
@endsection
