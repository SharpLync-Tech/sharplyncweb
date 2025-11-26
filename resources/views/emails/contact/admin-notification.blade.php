@extends('emails.contact.layout')

@section('content')
    <h2 style="margin:0 0 16px 0; font-size:20px; color:#0A2A4D;">
        New contact form message
    </h2>

    <p style="margin:0 0 16px 0; font-size:14px; color:#1A3152;">
        You’ve received a new enquiry from the SharpLync website contact form.
    </p>

    <table cellpadding="0" cellspacing="0" style="width:100%; font-size:14px; margin-bottom:18px;">
        <tr>
            <td style="padding:4px 0; width:110px; font-weight:bold; color:#0A2A4D;">Name</td>
            <td style="padding:4px 0; color:#1A3152;">{{ $name }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0; font-weight:bold; color:#0A2A4D;">Email</td>
            <td style="padding:4px 0; color:#1A3152;">{{ $email }}</td>
        </tr>
        @if(!empty($phone))
            <tr>
                <td style="padding:4px 0; font-weight:bold; color:#0A2A4D;">Phone</td>
                <td style="padding:4px 0; color:#1A3152;">{{ $phone }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:4px 0; font-weight:bold; color:#0A2A4D;">Subject</td>
            <td style="padding:4px 0; color:#1A3152;">{{ $subject }}</td>
        </tr>
    </table>

    <h3 style="margin:0 0 8px 0; font-size:16px; color:#0A2A4D;">
        Message
    </h3>
    <p style="margin:0; font-size:14px; line-height:1.6; color:#1A3152; white-space:pre-line;">
        {{ $user_message }}
    </p>
@endsection
